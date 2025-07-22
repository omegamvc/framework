<?php

/**
 * Part of Omega - Application Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Application;

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Config\ConfigRepository;
use Omega\Container\Container;
use Omega\Container\Provider\AbstractServiceProvider;
use Omega\Http\Exceptions\HttpException;
use Omega\Http\Request;
use Omega\Support\PackageManifest;
use Omega\Support\RequestMacroServiceProvider;
use Omega\Support\Vite;
use Omega\View\Templator;

use function array_map;
use function defined;
use function file_exists;
use function in_array;
use function rtrim;

use const DIRECTORY_SEPARATOR;

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
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
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
    private array $providers = [];

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

        // set base path
        $this->setBasePath($basePath);
        $this->setConfigPath(
            $_ENV['CONFIG_PATH'] ?? DIRECTORY_SEPARATOR
            . 'app'
            . DIRECTORY_SEPARATOR
            . 'config'
            . DIRECTORY_SEPARATOR
        );

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
     * Defines legacy constants from the given configuration array.
     *
     * Useful for backward compatibility with global-based APIs (e.g., Redis, Memcached).
     *
     * @param array<string, string> $configs Associative array of constant names and values.
     * @return void
     */
    private function defined(array $configs): void
    {
        // redis
        defined('REDIS_HOST') || define('REDIS_HOST', $configs['REDIS_HOST']);
        defined('REDIS_PASS') || define('REDIS_PASS', $configs['REDIS_PASS']);
        defined('REDIS_PORT') || define('REDIS_PORT', $configs['REDIS_PORT']);
        // Memcached
        defined('MEMCACHED_HOST') || define('MEMCACHED_HOST', $configs['MEMCACHED_HOST']);
        defined('MEMCACHED_PASS') || define('MEMCACHED_PASS', $configs['MEMCACHED_PASS']);
        defined('MEMCACHED_PORT') || define('MEMCACHED_PORT', $configs['MEMCACHED_PORT']);
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
                'config'        => [ConfigRepository::class],
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
    public function loadConfig(ConfigRepository $configs): void
    {
        // give access to get config directly
        $this->set('config', fn (): ConfigRepository => $configs);

        // base env
        $this->set('environment', $configs['APP_ENV'] ?? $configs['ENVIRONMENT']);
        $this->set('app.debug', $configs['APP_DEBUG'] === 'true');
        // application path
        $this->setAppPath($this->getBasePath());
        $this->setModelPath($configs['MODEL_PATH']);
        $this->setViewPath($configs['VIEW_PATH']);
        $this->setViewPaths($configs['VIEW_PATHS']);
        $this->setControllerPath($configs['CONTROLLER_PATH']);
        $this->setServicesPath($configs['SERVICES_PATH']);
        $this->setComponentPath($configs['COMPONENT_PATH']);
        $this->setCommandPath($configs['COMMAND_PATH']);
        $this->setCachePath($configs['CACHE_PATH']);
        $this->setCompiledViewPath($configs['COMPILED_VIEW_PATH']);
        $this->setMiddlewarePath($configs['MIDDLEWARE']);
        $this->setProviderPath($configs['SERVICE_PROVIDER']);
        $this->setMigrationPath($configs['MIGRATION_PATH']);
        $this->setPublicPath($configs['PUBLIC_PATH']);
        $this->setSeederPath($configs['SEEDER_PATH']);
        $this->setStoragePath($configs['STORAGE_PATH']);
        // other config
        $this->set('config.pusher_id', $configs['PUSHER_APP_ID']);
        $this->set('config.pusher_key', $configs['PUSHER_APP_KEY']);
        $this->set('config.pusher_secret', $configs['PUSHER_APP_SECRET']);
        $this->set('config.pusher_cluster', $configs['PUSHER_APP_CLUSTER']);
        $this->set('config.view.extensions', $configs['VIEW_EXTENSIONS']);
        // load provider
        $this->providers = $configs['PROVIDERS'];
        $this->defined($configs->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function defaultConfigs(): array
    {
        return [
            // app config
            'BASEURL'               => '/',
            'time_zone'             => 'UTC',
            'APP_KEY'               => '',
            'ENVIRONMENT'           => 'dev',
            'APP_DEBUG'             => 'false',
            'BCRYPT_ROUNDS'         => 12,
            'CACHE_STORE'           => 'file',

            'COMMAND_PATH'          => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Commands' . DIRECTORY_SEPARATOR,
            'CONTROLLER_PATH'       => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR,
            'MODEL_PATH'            => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR,
            'MIDDLEWARE'            => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Middlewares' . DIRECTORY_SEPARATOR,
            'SERVICE_PROVIDER'      => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Providers' . DIRECTORY_SEPARATOR,
            'CONFIG'                => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR,
            'SERVICES_PATH'         => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR,
            'VIEW_PATH'             => DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR,
            'COMPONENT_PATH'        => DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR,
            'STORAGE_PATH'          => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR,
            'CACHE_PATH'            => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR,
            'CACHE_VIEW_PATH'       => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
            'PUBLIC_PATH'           => DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR,
            'MIGRATION_PATH'        => DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR,
            'SEEDER_PATH'           => DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR,

            'PROVIDERS'             => [
                // provider class name
            ],

            // db config
            'DB_HOST'               => 'localhost',
            'DB_USER'               => 'root',
            'DB_PASS'               => 'vb65ty4',
            'DB_NAME'               => 'phpmvc',

            // pusher
            'PUSHER_APP_ID'         => '',
            'PUSHER_APP_KEY'        => '',
            'PUSHER_APP_SECRET'     => '',
            'PUSHER_APP_CLUSTER'    => '',

            // redis driver
            'REDIS_HOST'            => '127.0.0.1',
            'REDIS_PASS'            => '',
            'REDIS_PORT'            => 6379,

            // Memcached
            'MEMCACHED_HOST'        => '127.0.0.1',
            'MEMCACHED_PASS'        => '',
            'MEMCACHED_PORT'        => 6379,

            // view config
            'VIEW_PATHS' => [
                DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR,
            ],
            'VIEW_EXTENSIONS' => [
                '.template.php',
                '.php',
            ],
            'COMPILED_VIEW_PATH' => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setBasePath(string $path): self
    {
        $this->basePath = $path;
        $this->set('path.base', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAppPath(string $path): self
    {
        $appPath = $path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;
        $this->set('path.app', $appPath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setModelPath(string $path): self
    {
        $modelPath = $this->basePath . $path;
        $this->set('path.model', $modelPath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewPath(string $path): self
    {
        $viewPath = $this->basePath . $path;
        $this->set('path.view', $viewPath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewPaths(array $paths): self
    {
        $viewPaths = array_map(fn ($path) => $this->basePath . $path, $paths);
        $this->set('paths.view', $viewPaths);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setControllerPath(string $path): self
    {
        $controllerPath = $this->basePath . $path;
        $this->set('path.controller', $controllerPath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setServicesPath(string $path): self
    {
        $servicesPath = $this->basePath . $path;
        $this->set('path.services', $servicesPath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setComponentPath(string $path): self
    {
        $componentPath = $this->basePath . $path;
        $this->set('path.component', $componentPath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCommandPath(string $path): self
    {
        $commandPath = $this->basePath . $path;
        $this->set('path.command', $commandPath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStoragePath(string $path): self
    {
        $storagePath = $this->basePath . $path;
        $this->set('path.storage', $storagePath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCachePath(string $path): self
    {
        $cachePath = $this->basePath . $path;
        $this->set('path.cache', $cachePath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCompiledViewPath(string $path): self
    {
        $compiledViewPath = $this->basePath . $path;
        $this->set('path.compiled_view_path', $compiledViewPath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfigPath(string $path): self
    {
        $configPath = $this->basePath . $path;
        $this->set('path.config', $configPath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMiddlewarePath(string $path): self
    {
        $middlewarePath = $this->basePath . $path;
        $this->set('path.middleware', $middlewarePath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setProviderPath(string $path): self
    {
        $serviceProviderPath = $this->basePath . $path;
        $this->set('path.provider', $serviceProviderPath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMigrationPath(string $path): self
    {
        $migrationPath = $this->basePath . $path;
        $this->set('path.migration', $migrationPath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSeederPath(string $path): self
    {
        $seederPath = $this->basePath . $path;
        $this->set('path.seeder', $seederPath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPublicPath(string $path): self
    {
        $publicPath = $this->basePath . $path;
        $this->set('path.public', $publicPath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBasePath(): string
    {
        return $this->get('path.base');
    }

    /**
     * {@inheritdoc}
     */
    public function getAppPath(): string
    {
        return $this->get('path.app');
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicationCachePath(): string
    {
        return rtrim(
            $this->getBasePath(),
            DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
    }

    /**
     * {@inheritdoc}
     */
    public function getModelPath(): string
    {
        return $this->get('path.model');
    }

    /**
     * {@inheritdoc}
     */
    public function getViewPath(): string
    {
        return $this->get('path.view');
    }

    /**
     * {@inheritdoc}
     */
    public function getViewPaths(): array
    {
        return $this->get('paths.view');
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerPath(): string
    {
        return $this->get('path.controller');
    }

    /**
     * {@inheritdoc}
     */
    public function getServicesPath(): string
    {
        return $this->get('path.services');
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentPath(): string
    {
        return $this->get('path.component');
    }

    /**
     * {@inheritdoc}
     */
    public function getCommandPath(): string
    {
        return $this->get('path.command');
    }

    /**
     * {@inheritdoc}
     */
    public function getStoragePath(): string
    {
        return $this->get('path.storage');
    }

    /**
     * {@inheritdoc}
     */
    public function getCachePath(): string
    {
        return $this->get('path.cache');
    }

    /**
     * {@inheritdoc}
     */
    public function getCompiledViewPath(): string
    {
        return $this->get('path.compiled_view_path');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath(): string
    {
        return $this->get('path.config');
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddlewarePath(): string
    {
        return $this->get('path.middleware');
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderPath(): string
    {
        return $this->get('path.provider');
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationPath(): string
    {
        return $this->get('path.migration');
    }

    /**
     * {@inheritdoc}
     */
    public function getSeederPath(): string
    {
        return $this->get('path.seeder');
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicPath(): string
    {
        return $this->get('path.public');
    }

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
        return file_exists($this->getStoragePath() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');
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

        if (false === file_exists($down = $this->getStoragePath() . 'app' . DIRECTORY_SEPARATOR . 'down')) {
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
