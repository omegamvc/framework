<?php

declare(strict_types=1);

namespace Omega\Application;

use App\Providers\AppServiceProvider;
use Exception;
use Omega\Cache\CacheServiceProvider;
use Omega\Config\ConfigRepository;
use Omega\Container\Container;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Container\Invoker\Exception\InvocationException;
use Omega\Container\Invoker\Exception\NotCallableException;
use Omega\Container\Invoker\Exception\NotEnoughParametersException;
use Omega\Container\Provider\AbstractServiceProvider;
use Omega\Database\DatabaseServiceProvider;
use Omega\Http\Exceptions\HttpException;
use Omega\Http\Request;
use Omega\Router\RouteServiceProvider;
use Omega\Support\AddonServiceProvider;
use Omega\Support\PackageManifest;
use Omega\Support\Vite;
use Omega\View\Templator;
use Omega\View\ViewServiceProvider;

use function array_map;
use function count;
use function file_exists;
use function in_array;

final class Application extends Container
{
    /** @var Application|null Application instance. */
    private static ?Application $app;

    /** @var string Base path. */
    private string $basePath;

    /** @var AbstractServiceProvider[] All service provider. */
    private array $providers = [
        AppServiceProvider::class,
        RouteServiceProvider::class,
        DatabaseServiceProvider::class,
        ViewServiceProvider::class,
        CacheServiceProvider::class,
    ];

    /** @var AbstractServiceProvider[] Booted service provider. */
    private array $bootedProviders = [];

    /** @var AbstractServiceProvider[] Loaded service provider. */
    private array $loadedProviders = [];

    /** @var bool Detect application has been booted. */
    private bool $isBooted = false {
        get {
            return $this->isBooted;
        }
    }

    /** @var bool Detect application has been bootstrapped. */
    private bool $isBootstrapped = false {
        get {
            return $this->isBootstrapped;
        }
    }

    /** @var callable[] Terminate callback register. */
    private array $terminateCallback = [];

    /** @var callable[] Registered booting callback. */
    protected array $bootingCallbacks = [];

    /** @var callable[] Registered booted callback. */
    protected array $bootedCallbacks = [];

    /**
     * Constructor.
     *
     * @param string $basePath application path
     * @throws Exception
     */
    public function __construct(string $basePath)
    {
        parent::__construct();

        $this->basePath = $basePath;

        $this->set('path.base', $this->basePath . DIRECTORY_SEPARATOR);

        $this->set('path.config', $this->basePath . set_path('config'));

        $this->setBaseBinding();

        $this->register(AddonServiceProvider::class);

        $this->registerAlias();

        foreach ($this->definitions() as $key => $value) {
            $this->set($key, $value);
        }
    }

    protected function definitions(): array
    {
        return [
            'boot.cache'              => $this->basePath . set_path('bootstrap.cache'),
            'path.app'                => $this->basePath . set_path('app'),
            'path.cache'              => $this->basePath . set_path('storage.app.cache'),
            'path.command'            => $this->basePath . set_path('app.Console.Commands'),
            'path.component'          => $this->basePath . set_path('resources.components'),
            'path.controller'         => $this->basePath . set_path('app.Http.Controllers'),
            'path.exception'          => $this->basePath . set_path('app.Exceptions'),
            'path.model'              => $this->basePath . set_path('app.Models'),
            'path.middleware'         => $this->basePath . set_path('app.Http.Middlewares'),
            'path.provider'           => $this->basePath . set_path('app.Providers'),
            'path.view'               => $this->basePath . set_path('resources.views'),
            'path.storage'            => $this->basePath . set_path('storage'),
            'path.public'             => $this->basePath . set_path('public'),
            'path.migration'          => $this->basePath . set_path('database.migration'),
            'path.seeder'             => $this->basePath . set_path('database.seeders'),
            'path.compiled_view_path' => $this->basePath . set_path('storage.app.view'),
            'path.database'           => $this->basePath . set_path('database'),
            'paths.view'              => array_map(
                fn ($p) => $this->basePath . $p,
                [set_path('resources.views')]
            ),
            'environment'             => env('APP_ENV'),
            'app.debug'               => env('APP_DEBUG'),
        ];
    }

    /**
     * Get instance Application container.
     *
     * @return Application|null
     */
    public static function getInstance(): ?Application
    {
        return Application::$app;
    }

    /**
     * Register base binding container.
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
     * Load and set Configuration to application.
     *
     * @param ConfigRepository $configs
     * @return void
     */
    public function loadConfig(ConfigRepository $configs): void
    {
        $this->set('config', fn (): ConfigRepository => $configs);

        $this->set('config.view.extensions', $configs['VIEW_EXTENSIONS']);
    }

    /**
     * Get application (bootstrapper) cache path.
     * default './boostrap/cache/'.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidDefinitionException
     */
    public function getApplicationCachePath(): string
    {
        $base = rtrim(get_path('path.base'), "/\\");

        return $base . set_path('bootstrap.cache');
    }

    /**
     * Detect application environment.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidDefinitionException
     */
    public function getEnvironment(): string
    {
        return $this->get('environment');
    }

    /**
     * Detect application debug enable.
     *
     * @return bool
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidDefinitionException
     */
    public function isDebugMode(): bool
    {
        return $this->get('app.debug');
    }

    /**
     * Detect application production mode.
     *
     * @return bool
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidDefinitionException
     */
    public function isProduction(): bool
    {
        return $this->getEnvironment() === 'prod';
    }

    /**
     * Detect application development mode.
     *
     * @return bool
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidDefinitionException
     */
    public function isDev(): bool
    {
        return $this->getEnvironment() === 'dev';
    }

    // core region

    /**
     * Bootstrapper.
     *
     * @param array<int, class-string> $bootstrappers
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidDefinitionException
     */
    public function bootstrapWith(array $bootstrappers): void
    {
        $this->isBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }
    }

    /**
     * Boot service provider.
     *
     * @return void
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     * @throws NotFoundException
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
     * Register service providers.
     *
     * @return void
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotEnoughParametersException
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
     * Call the registered booting callbacks.
     *
     * @param callable[] $bootCallBacks
     * @return void
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotEnoughParametersException
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
     * Add booted call back, call after boot is called.
     *
     * @param callable $callback
     * @return void
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     */
    public function bootedCallback(callable $callback): void
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted) {
            $this->call($callback);
        }
    }

    /**
     * Flush or reset application (static).
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
     * Register service provider.
     *
     * @param string $provider Class-name service provider
     * @return AbstractServiceProvider
     */
    public function register(string $provider): AbstractServiceProvider
    {
        $providerClassName = $provider;
        $provider           = new $provider($this);

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
     * Terminate the application.
     *
     * @return void
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotEnoughParametersException
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
     * Determinate application maintenance mode.
     *
     * @return bool
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    public function isDownMaintenanceMode(): bool
    {
        return file_exists(
            get_path('path.storage')
            . 'app'
            . DIRECTORY_SEPARATOR
            . 'maintenance.php'
        );
    }

    /**
     * Get down maintenance file config.
     *
     * @return array<string, string|int|null>
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    public function getDownData(): array
    {
        $default = [
            'redirect' => null,
            'retry'    => null,
            'status'   => 503,
            'template' => null,
        ];

        if (false === file_exists($down = get_path('path.storage') . 'app' . DIRECTORY_SEPARATOR . 'down')) {
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
     * Abort application to http exception.
     *
     * @param int $code
     * @param string $message
     * @param array<string, string> $headers
     * @return void
     */
    public function abort(int $code, string $message = '', array $headers = []): void
    {
        throw new HttpException($code, $message, null, $headers);
    }

    /**
     * Register aliases to container.
     *
     * @return void
     * @throws Exception
     */
    protected function registerAlias(): void
    {
        foreach ([
            'request'       => [Request::class],
            'view.instance' => [Templator::class],
            'vite.gets'     => [Vite::class],
            'config'        => [ConfigRepository::class],
        ] as $abstract => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($abstract, $alias);
            }
        }
    }

    /**
     * Merge application provider and vendor package provider.
     *
     * @return AbstractServiceProvider[]
     * @throws DependencyException
     * @throws NotFoundException|
     * @throws InvalidDefinitionException
     */
    protected function getMergeProviders(): array
    {
        return [...$this->providers, ...$this->make(PackageManifest::class)->providers()];
    }
}
