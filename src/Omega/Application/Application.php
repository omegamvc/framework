<?php

declare(strict_types=1);

namespace Omega\Application;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Omega\Config\ConfigRepository;
use Omega\Container\Container;
use Omega\Container\Provider\AbstractServiceProvider;
use Omega\Http\Request;
use Omega\Http\Exceptions\HttpException;
use Omega\Support\AddonServiceProvider;
use Omega\Support\PackageManifest;
use Omega\Support\Vite;
use Omega\View\Templator;
use function array_map;
use function count;
use function defined;
use function file_exists;
use function in_array;

final class Application extends Container
{
    /** @var Application|null Application instance. */
    private static ?Application $app;

    /** @var string Base path. */
    private string $basePath;

    /** @var AbstractServiceProvider[] All service provider. */
    private array $providers = [];

    /** @var AbstractServiceProvider[] Booted service provider. */
    private array $bootedProviders = [];

    /** @var AbstractServiceProvider[] Loaded service provider. */
    private array $loadedProviders = [];

    /** @var bool Detect application has been booted. */
    private bool $isBooted = false;

    /** @var bool Detect application has been bootstrapped. */
    private bool $isBootstrapped = false;

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

        // set base path
        $this->setBasePath($basePath);
        $this->setConfigPath($_ENV['CONFIG_PATH']
            ?? DIRECTORY_SEPARATOR
            . 'app'
            . DIRECTORY_SEPARATOR
            . 'config'
            . DIRECTORY_SEPARATOR
        );

        // base binding
        $this->setBaseBinding();

        // register base provider
        $this->register(AddonServiceProvider::class);

        // register container alias
        $this->registerAlias();
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
     * @throws DependencyException
     * @throws NotFoundException
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
        $this->legacyApi($configs->getAll());
    }

    /**
     * Default config, prevent for empty config.
     *
     * @return array<string, mixed> Configs
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
            'SERVICES_PATH'         => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR,
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
            'DB_PASS'               => '',
            'DB_NAME'               => '',

            // pusher
            'PUSHER_APP_ID'         => '',
            'PUSHER_APP_KEY'        => '',
            'PUSHER_APP_SECRET'     => '',
            'PUSHER_APP_CLUSTER'    => '',

            // redis driver
            'REDIS_HOST'            => '127.0.0.1',
            'REDIS_PASS'            => '',
            'REDIS_PORT'            => 6379,

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
     * Helper add define for legacy API.
     *
     * @param array<string, string> $configs Array configuration
     * @return void
     */
    private function legacyApi(array $configs): void
    {
        // redis
        defined('REDIS_HOST') || define('REDIS_HOST', $configs['REDIS_HOST']);
        defined('REDIS_PASS') || define('REDIS_PASS', $configs['REDIS_PASS']);
        defined('REDIS_PORT') || define('REDIS_PORT', $configs['REDIS_PORT']);
        // memcache

        defined('MEMCACHED_HOST') || define('MEMCACHED_HOST', $configs['MEMCACHED_HOST']);
        defined('MEMCACHED_PASS') || define('MEMCACHED_PASS', $configs['MEMCACHED_PASS']);
        defined('MEMCACHED_PORT') || define('MEMCACHED_PORT', $configs['MEMCACHED_PORT']);
    }

    /**
     * Set Base path.
     *
     * @param string $path Base path
     * @return self
     */
    public function setBasePath(string $path): self
    {
        $this->basePath = $path;

        $this->set('path.base', $path);

        return $this;
    }

    /**
     * Set app path.
     *
     * @param string $path App path
     * @return self
     */
    public function setAppPath(string $path): self
    {
        $appPath = $path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;
        $this->set('path.app', $appPath);

        return $this;
    }

    /**
     * Set model path.
     *
     * @param string $path Model path
     * @return self
     */
    public function setModelPath(string $path): self
    {
        $modelPath = $this->basePath . $path;
        $this->set('path.model', $modelPath);

        return $this;
    }

    /**
     * Set base view path.
     *
     * @param string $path Base view path
     * @return self
     */
    public function setViewPath(string $path): self
    {
        $viewPath = $this->basePath . $path;
        $this->set('path.view', $viewPath);

        return $this;
    }

    /**
     * Set view paths.
     *
     * @param string[] $paths View paths
     * @return self
     */
    public function setViewPaths(array $paths): self
    {
        $viewPaths = array_map(fn ($path) => $this->basePath . $path, $paths);
        $this->set('paths.view', $viewPaths);

        return $this;
    }

    /**
     * Set controller path.
     *
     * @param string $path Controller path
     * @return self
     */
    public function setControllerPath(string $path): self
    {
        $controllerPath = $this->basePath . $path;
        $this->set('path.controller', $controllerPath);

        return $this;
    }

    /**
     * Set services path.
     *
     * @param string $path Services path
     * @return self
     */
    public function setServicesPath(string $path): self
    {
        $servicesPath = $this->basePath . $path;
        $this->set('path.services', $servicesPath);

        return $this;
    }

    /**
     * Set component path.
     *
     * @param string $path Component path
     * @return self
     */
    public function setComponentPath(string $path): self
    {
        $componentPath = $this->basePath . $path;
        $this->set('path.component', $componentPath);

        return $this;
    }

    /**
     * Set command path.
     *
     * @param string $path Command path
     * @return self
     */
    public function setCommandPath(string $path): self
    {
        $commandPath = $this->basePath . $path;
        $this->set('path.command', $commandPath);

        return $this;
    }

    /**
     * Set storage path.
     *
     * @param string $path Storage path
     * @return self
     */
    public function setStoragePath(string $path): self
    {
        $storagePath = $this->basePath . $path;
        $this->set('path.storage', $storagePath);

        return $this;
    }

    /**
     * Set cache path.
     *
     * @param string $path Cache path
     * @return self
     */
    public function setCachePath(string $path): self
    {
        $cachePath = $this->basePath . $path;
        $this->set('path.cache', $cachePath);

        return $this;
    }

    /**
     * Set compiled view path.
     *
     * @param string $path Compiled view path
     * @return self
     */
    public function setCompiledViewPath(string $path): self
    {
        $compiledViewPath = $this->basePath . $path;
        $this->set('path.compiled_view_path', $compiledViewPath);

        return $this;
    }

    /**
     * Set config path.
     *
     * @param string $path config path
     * @return self
     */
    public function setConfigPath(string $path): self
    {
        $configPath = $this->basePath . $path;
        $this->set('path.config', $configPath);

        return $this;
    }

    /**
     * Set middleware path.
     *
     * @param string $path middleware path
     * @return self
     */
    public function setMiddlewarePath(string $path): self
    {
        $middlewarePath = $this->basePath . $path;
        $this->set('path.middleware', $middlewarePath);

        return $this;
    }

    /**
     * Set services provider path.
     *
     * @param string $path services path
     * @return self
     */
    public function setProviderPath(string $path): self
    {
        $serviceProviderPath = $this->basePath . $path;
        $this->set('path.provider', $serviceProviderPath);

        return $this;
    }

    /**
     * Set migration path.
     *
     * @param string $path migration path
     * @return self
     */
    public function setMigrationPath(string $path): self
    {
        $migrationPath = $this->basePath . $path;
        $this->set('path.migration', $migrationPath);

        return $this;
    }

    /**
     * Set seeder path.
     *
     * @param string $path seeder path
     * @return self
     */
    public function setSeederPath(string $path): self
    {
        $seederPath = $this->basePath . $path;
        $this->set('path.seeder', $seederPath);

        return $this;
    }

    /**
     * Set public path.
     *
     * @param string $path
     * @return self
     */
    public function setPublicPath(string $path): self
    {
        $publicPath = $this->basePath . $path;
        $this->set('path.public', $publicPath);

        return $this;
    }

    /**
     * Get base path/dir.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getBasePath(): string
    {
        return $this->get('path.base');
    }

    /**
     * Get app path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getAppPath(): string
    {
        return $this->get('path.app');
    }

    /**
     * Get application (bootstrapper) cache path.
     * default './boostrap/cache/'.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getApplicationCachePath(): string
    {
        return rtrim($this->getBasePath(),
                DIRECTORY_SEPARATOR
            )
            . DIRECTORY_SEPARATOR
            . 'bootstrap'
            . DIRECTORY_SEPARATOR
            . 'cache'
            . DIRECTORY_SEPARATOR;
    }

    /**
     * Get model path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getModelPath(): string
    {
        return $this->get('path.model');
    }

    /**
     * Get base view path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getViewPath(): string
    {
        return $this->get('path.view');
    }

    /**
     * Get view paths.
     *
     * @return string[]
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getViewPaths(): array
    {
        return $this->get('paths.view');
    }

    /**
     * Get controller path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getControllerPath(): string
    {
        return $this->get('path.controller');
    }

    /**
     * Get Services path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getServicesPath(): string
    {
        return $this->get('path.services');
    }

    /**
     * Get component path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getComponentPath(): string
    {
        return $this->get('path.component');
    }

    /**
     * Get command path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getCommandPath(): string
    {
        return $this->get('path.command');
    }

    /**
     * Get storage path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getStoragePath(): string
    {
        return $this->get('path.storage');
    }

    /**
     * Get cache path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getCachePath(): string
    {
        return $this->get('path.cache');
    }

    /**
     * Get compiled path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getCompiledViewPath(): string
    {
        return $this->get('path.compiled_view_path');
    }

    /**
     * Get config path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getConfigPath(): string
    {
        return $this->get('path.config');
    }

    /**
     * Get middleware path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getMiddlewarePath(): string
    {
        return $this->get('path.middleware');
    }

    /**
     * Get provider path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getProviderPath(): string
    {
        return $this->get('path.provider');
    }

    /**
     * Get migration path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getMigrationPath(): string
    {
        return $this->get('path.migration');
    }

    /**
     * Get seeder path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getSeederPath(): string
    {
        return $this->get('path.seeder');
    }

    /**
     * Get public path.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getPublicPath(): string
    {
        return $this->get('path.public');
    }

    /**
     * Detect application environment.
     *
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function environment(): string
    {
        return $this->get('environment');
    }

    /**
     * Detect application debug enable.
     *
     * @return bool
     * @throws DependencyException
     * @throws NotFoundException
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
     */
    public function isProduction(): bool
    {
        return $this->environment() === 'prod';
    }

    /**
     * Detect application development mode.
     *
     * @return bool
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function isDev(): bool
    {
        return $this->environment() === 'dev';
    }

    /**
     * Detect application has been booted.
     *
     * @return bool
     */
    public function isBooted(): bool
    {
        return $this->isBooted;
    }

    /**
     * Detect application has been bootstrapped.
     *
     * @return bool
     */
    public function isBootstrapped(): bool
    {
        return $this->isBootstrapped;
    }

    // core region

    /**
     * Bootstrapper.
     *
     * @param array<int, class-string> $bootstrappers
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
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
     * @throws NotFoundException
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
     * Add booting call back, call before boot is calling.
     *
     * @param callable $callback
     * @return void
     */
    public function bootingCallback(callable $callback): void
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Add booted call back, call after boot is called.
     *
     * @param callable $callback
     * @return void
     */
    public function bootedCallback(callable $callback): void
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
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
     * Register terminating callbacks.
     *
     * @param callable $terminateCallback
     * @return self
     */
    public function registerTerminate(callable $terminateCallback): self
    {
        $this->terminateCallback[] = $terminateCallback;

        return $this;
    }

    /**
     * Terminate the application.
     *
     * @return void
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
     * @throws NotFoundException
     */
    public function isDownMaintenanceMode(): bool
    {
        return file_exists(
            $this->getStoragePath()
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
     * @throws NotFoundException
     */
    protected function getMergeProviders(): array
    {
        return [...$this->providers, ...$this->make(PackageManifest::class)->providers()];
    }
}
