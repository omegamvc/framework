<?php

/**
 * Part of Omega - Application Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Application;

use App\Providers\AppServiceProvider;
use App\Providers\CacheServiceProvider;
use App\Providers\DatabaseServiceProvider;
use App\Providers\RouteServiceProvider;
use App\Providers\ViewServiceProvider;
use DI\DependencyException;
use DI\NotFoundException;
use Omega\Config\Config;
use Omega\Container\Container;
use Omega\Container\Provider\AbstractServiceProvider;
use Omega\Http\Exceptions\HttpException;
use Omega\Http\Request;
use Omega\Support\PackageManifest;
use Omega\Support\Path;
use Omega\Support\RequestMacroServiceProvider;
use Omega\Support\Vite;
use Omega\View\Templator;

use function array_map;
use function file_exists;
use function in_array;

/**
 * The core Application class.
 *
 * This class serves as the main entry point of the application lifecycle.
 * It manages path resolution, service provider registration, container bindings,
 * boot and bootstrap sequences, as well as legacy constant definitions and alias handling.
 *
 * Acts as a wrapper around the dependency injection container,
 * and centralizes application state and configuration.
 *
 * @category  Omega
 * @package   Application
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class Application extends Container implements ApplicationInterface
{
    /** @var Application|null The singleton application instance. */
    private static ?Application $app = null;

    /** @var string The base path of the application. */
    private string $basePath;

    /** @var AbstractServiceProvider[] List of all registered service providers. */
    private array $providers = [
        AppServiceProvider::class,
        RouteServiceProvider::class,
        DatabaseServiceProvider::class,
        ViewServiceProvider::class,
        CacheServiceProvider::class,
    ];

    /** @var AbstractServiceProvider[] List of service providers that have been booted. */
    private array $bootedProviders = [];

    /** @var AbstractServiceProvider[] List of service providers that have been loaded. */
    private array $loadedProviders = [];

    /** @var bool Indicates whether the application has completed the boot process. */
    private bool $isBooted = false;

    /** @var bool Indicates whether the application has completed the bootstrap process. */
    private bool $isBootstrapped = false;

    /** @var callable[] List of callbacks to run when the application is terminated. */
    private array $terminateCallback = [];

    /** @var callable[] List of callbacks to run before the application boots. */
    protected array $bootingCallbacks = [];

    /** @var callable[] List of callbacks to run after the application has booted. */
    protected array $bootedCallbacks = [];

    /**
     * Constructs the application instance.
     *
     * Initializes paths, base bindings, service providers, and container aliases.
     *
     * @param string $basePath The root directory of the application.
     */
    public function __construct(string $basePath)
    {
        parent::__construct();

        $this->basePath = $basePath;

        Path::init($this->basePath);

        $this->setBasePath($this->basePath);
        $this->setConfigPath(env('CONFIG_PATH'));

        // base binding
        $this->setBaseBinding();

        // register base provider
        $this->register(RequestMacroServiceProvider::class);

        // register container alias
        $this->registerAlias();
    }

    /**
     * Retrieves the singleton instance of the application.
     *
     * @return Application|null The application instance, or null if not set.
     */
    public static function getInstance(): ?Application
    {
        return Application::$app;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): string
    {
        return static::VERSION;
    }

    /**
     * Registers the core bindings into the container.
     *
     * Sets the application instance and container aliases, and registers the package manifest.
     *
     * @return void
     */
    protected function setBaseBinding(): void
    {
        Application::$app = $this;
        $this->set('app', $this);
        $this->set(Application::class, $this);
        $this->set(Container::class, $this);

        $this->set(
            PackageManifest::class,
            fn () => new PackageManifest(
                $this->basePath,
                $this->getApplicationCachePath()
            )
        );
    }

    /**
     * Registers aliases for container services.
     *
     * Maps common string-based identifiers to their corresponding classes
     * within the container for easier access.
     *
     * @return void
     */
    protected function registerAlias(): void
    {
        foreach (
            [
                'request'       => [Request::class],
                'view.instance' => [Templator::class],
                'vite.gets'     => [Vite::class],
                'config'        => [Config::class],
            ] as $abstract => $aliases
        ) {
            foreach ($aliases as $alias) {
                $this->alias($abstract, $alias);
            }
        }
    }

    /**
     * Merges core application providers with vendor-defined package providers.
     *
     * @return AbstractServiceProvider[] The combined list of providers.
     * @throws DependencyException If a service could not be resolved.
     * @throws NotFoundException If a class or value was not found in the container.
     */
    protected function getMergeProviders(): array
    {
        return [...$this->providers, ...$this->make(PackageManifest::class)->providers()];
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfig(Config $configs): void
    {
        $this->set('config', fn (): Config => $configs);

        $this->set('environment', env('APP_ENV'));
        $this->set('app.debug', env('APP_DEBUG'));

        $this->set('config.view.extensions', $configs['VIEW_EXTENSIONS']);
    }

    #region Setter Region
    /**
     * {@inheritdoc}
     */
    public function setBasePath(string $path): static
    {
        $this->set('path.base', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAppPath(string $path): static
    {
        $this->set('path.app', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setModelPath(string $path): static
    {
        $this->set('path.model', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewPath(string $path): static
    {
        $this->set('path.view', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewPaths(array $paths): static
    {
        $this->set('paths.view', array_map(fn ($path) => $path, $paths));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setControllerPath(string $path): static
    {
        $this->set('path.controller', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setServicesPath(string $path): static
    {
        $this->set('path.services', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setComponentPath(string $path): static
    {
        $this->set('path.component', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCommandPath(string $path): static
    {
        $this->set('path.command', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStoragePath(string $path): static
    {
        $this->set('path.storage', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCachePath(string $path): static
    {
        $this->set('path.cache', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCompiledViewPath(string $path): static
    {
        $this->set('path.compiled_view_path', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfigPath(string $path): static
    {
        $this->set('path.config', Path::getPath($path));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMiddlewarePath(string $path): static
    {
        $this->set('path.middleware', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setProviderPath(string $path): static
    {
        $this->set('path.provider', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMigrationPath(string $path): static
    {
        $this->set('path.migration', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSeederPath(string $path): static
    {
        $this->set('path.seeder', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPublicPath(string $path): static
    {
        $this->set('path.public', $path);

        return $this;
    }
    #endregion

    #region Getter Region
    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getBasePath(): string
    {
        return $this->get('path.base');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getAppPath(?string $path = null): string
    {
        if (! $this->has('path.app')) {
            $this->setAppPath(Path::getPath($path ?? 'app'));
        }

        return $this->get('path.app');
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicationCachePath(): string
    {
        return Path::getPath('bootstrap.cache');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getModelPath(?string $path = null): string
    {
        if (! $this->has('path.model')) {
            $this->setModelPath(Path::getPath($path ?? 'app.Models'));
        }

        return $this->get('path.model');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getViewPath(?string $path = null): string
    {
        if (! $this->has('path.view')) {
            $this->setViewPath(Path::getPath($path ?? 'resources.views'));
        }

        return $this->get('path.view');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getViewPaths(?array $paths = null): array
    {
        if (! $this->has('paths.view')) {
            $defaultPaths = $paths ?? [Path::getPath('resources.views')];
            $this->setViewPaths($defaultPaths);
        }

        return $this->get('paths.view');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getControllerPath(?string $path = null): string
    {
        if (! $this->has('path.controller')) {
            $this->setControllerPath(Path::getPath($path ?? 'app.Controllers'));
        }

        return $this->get('path.controller');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getServicesPath(?string $path = null): string
    {
        if (! $this->has('path.services')) {
            $this->setServicesPath(Path::getPath($path ?? 'app.services'));
        }

        return $this->get('path.services');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getComponentPath(?string $path = null): string
    {
        if (! $this->has('path.component')) {
            $this->setComponentPath(Path::getPath($path ?? 'resources.components'));
        }

        return $this->get('path.component');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getCommandPath(?string $path = null): string
    {
        if (! $this->has('path.command')) {
            $this->setCommandPath(Path::getPath($path ?? 'app.Commands'));
        }

        return $this->get('path.command');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getStoragePath(?string $path = null): string
    {
        if (! $this->has('path.storage')) {
            $this->setStoragePath(Path::getPath($path ?? 'storage'));
        }

        return $this->get('path.storage');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getCachePath(?string $path = null): string
    {
        if (! $this->has('path.cache')) {
            $this->setCachePath(Path::getPath($path ?? 'storage.app.cache'));
        }

        return $this->get('path.cache');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getCompiledViewPath(?string $path = null): string
    {
        if (! $this->has('path.compiled_view_path')) {
            $this->setCompiledViewPath(Path::getPath($path ?? 'storage.app.view'));
        }

        return $this->get('path.compiled_view_path');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getConfigPath(): string
    {
        return $this->get('path.config');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getMiddlewarePath(?string $path = null): string
    {
        if (! $this->has('path.middleware')) {
            $this->setMiddlewarePath(Path::getPath($path ?? 'app.Middlewares'));
        }

        return $this->get('path.middleware');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getProviderPath(?string $path = null): string
    {
        if (! $this->has('path.provider')) {
            $this->setProviderPath(Path::getPath($path ?? 'app.Providers'));
        }

        return $this->get('path.provider');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getMigrationPath(?string $path = null): string
    {
        if (! $this->has('path.migration')) {
            $this->setMigrationPath(Path::getPath($path ?? 'database.migration'));
        }

        return $this->get('path.migration');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getSeederPath(?string $path = null): string
    {
        if (! $this->has('path.seeder')) {
            $this->setSeederPath(Path::getPath($path ?? 'database.seeders'));
        }

        return $this->get('path.seeder');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DependencyException if a dependency cannot be resolved.
     * @throws NotFoundException if a requested entry is not found in the container.
     */
    public function getPublicPath(?string $path = null): string
    {
        if (! $this->has('path.public')) {
            $this->setPublicPath(Path::getPath($path ?? 'public'));
        }

        return $this->get('path.public');
    }
    #endregion

    /**
     * {@inheritdoc}
     */
    public function getEnvironment(): string
    {
        return $this->get('environment');
    }

    /**
     * {@inheritdoc}
     */
    public function isDebugMode(): bool
    {
        return $this->get('app.debug');
    }

    /**
     * {@inheritdoc}
     */
    public function isProduction(): bool
    {
        return $this->getEnvironment() === 'prod';
    }

    /**
     * {@inheritdoc}
     */
    public function isDev(): bool
    {
        return $this->getEnvironment() === 'dev';
    }

    /**
     * {@inheritdoc}
     */
    public function isBooted(): bool
    {
        return $this->isBooted;
    }

    /**
     * {@inheritdoc}
     */
    public function isBootstrapped(): bool
    {
        return $this->isBootstrapped;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrapWith(array $providers): void
    {
        $this->isBootstrapped = true;

        foreach ($providers as $provider) {
            $this->make($provider)->bootstrap($this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function bootProvider(): void
    {
        if ($this->isBooted) {
            return;
        }

        $this->callBootCallbacks($this->bootingCallbacks);

        foreach ($this->getMergeProviders() as $provider) {
            if (in_array($provider, $this->bootedProviders)) {
                continue;
            }

            $this->call([$provider, 'boot']);
            $this->bootedProviders[] = $provider;
        }

        $this->callBootCallbacks($this->bootedCallbacks);

        $this->isBooted = true;
    }

    /**
     * {@inheritdoc}
     */
    public function registerProvider(): void
    {
        foreach ($this->getMergeProviders() as $provider) {
            if (in_array($provider, $this->loadedProviders)) {
                continue;
            }

            $this->call([$provider, 'register']);

            $this->loadedProviders[] = $provider;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function callBootCallbacks(array $bootCallBacks): void
    {
        $index = 0;

        while ($index < count($bootCallBacks)) {
            $this->call($bootCallBacks[$index]);

            $index++;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function bootingCallback(callable $callback): void
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function bootedCallback(callable $callback): void
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $this->call($callback);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        Application::$app = null;

        $this->providers         = [];
        $this->loadedProviders   = [];
        $this->bootedProviders   = [];
        $this->terminateCallback = [];
        $this->bootingCallbacks  = [];
        $this->bootedCallbacks   = [];

        parent::flush();
    }

    /**
     * {@inheritdoc}
     */
    public function register(string $provider): AbstractServiceProvider
    {
        $providerClassName = $provider;
        $provider          = new $provider($this);

        $provider->register();
        $this->loadedProviders[] = $providerClassName;

        if ($this->isBooted) {
            $provider->boot();
            $this->bootedProviders[] = $providerClassName;
        }

        $this->providers[] = $providerClassName;

        return $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function registerTerminate(callable $terminateCallback): self
    {
        $this->terminateCallback[] = $terminateCallback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(): void
    {
        $index = 0;

        while ($index < count($this->terminateCallback)) {
            $this->call($this->terminateCallback[$index]);

            $index++;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isDownMaintenanceMode(): bool
    {
        return file_exists($this->getStoragePath() . Path::getPath('app', 'maintenance.php'));
    }

    /**
     * {@inheritdoc}
     */
    public function getDownData(): array
    {
        $default = [
            'redirect' => null,
            'retry'    => null,
            'status'   => 503,
            'template' => null,
        ];

        $down = $this->getStoragePath() . Path::getPath('app', 'down');

        if (!file_exists($down)) {
            return $default;
        }

        /** @var array<string, string|int|null> $config */
        $config = include $down;

        foreach ($config as $key => $value) {
            $default[$key] = $value;
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function abort(int $code, string $message = '', array $headers = []): void
    {
        throw new HttpException($code, $message, null, $headers);
    }
}
