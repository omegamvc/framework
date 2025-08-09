<?php

/**
 * Part of Omega - Application Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Application;

use Closure;
use Omega\Support\Singleton\SingletonTrait;
use Throwable;
use Omega\Container\Container;
use Omega\Container\Contracts\ServiceProvider\ServiceProviderInterface;
use Omega\Environment\Dotenv;
use Omega\Environment\EnvironmentDetector;
use Omega\Support\Facade\AliasLoader;
use Omega\Support\Facade\Facades\Router;
use Omega\Http\Response;
use Omega\Support\Path;
use Omega\Support\Str;

/**
 * Base application class.
 *
 * This `Application` class represents the main entry point of the Omega framework.
 * It manages the application's lifecycle, including configuration, routing, and
 * handling HTTP requests.
 *
 * @category  Omega
 * @package   Application
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */
class Application extends Container implements ApplicationInterface
{
    use SingletonTrait;

    /**
     * The base path for the Omega installation.
     *
     * @var string Holds the base path for the Omega installation.
     */
    protected string $basePath;

    /**
     * The environment file to load during bootstrapping.
     *
     * @var string Holds the environment file to load during bootstrapping.
     */
    protected string $environmentFile = '.env';

    /**
     * The custom environment path defined by the developer.
     *
     * @var string Holds the custom environment path defined by the developer.
     */
    protected string $environmentPath = '';

    /**
     * Application configuration.
     *
     * @var array<string, string> Holds the application configuration.
     */
    private array $app;

    /**
     * Application class constructor.
     *
     * @param  string|null $basePath Holds the Omega application base path or null.
     * @return void
     */
    private function __construct(?string $basePath = null)
    {
        //$this->basePath = rtrim($basePath ?? $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__, 5), '\/');

        $this->basePath = $this->basePath($basePath);
        Path::init($this->basePath);

        $this->alias('paths.base', fn() => Path::getPath());

        //$this->app = require Path::getPath('config', 'app.php');

        $this->configure();
        echo env('timezone');
        date_default_timezone_set(env('TIMEZONE'));

        $this->bindProviders();
        $this->registerFacades();
    }

    public function basePath(?string $basePath = null): string
    {
        return rtrim($basePath, '/\\');
    }

    /**
     * Bootstrap the application.
     *
     * This method starts and runs the Omega application. It handles the entire application lifecycle,
     * including session management, configuration setup, routing, and processing HTTP requests.
     *
     * @return Response Return an instance of Response representing the application's response.
     * @throws Throwable If an error occurs during application execution.
     */
    public function bootstrap(): Response
    {
        return $this->dispatch();
    }

    /**
     * Configure the application.
     *
     * This method sets up the application's configuration by loading environment
     * variables from Dotenv.
     *
     * @return void
     */
    private function configure(): void
    {
        Dotenv::load(Path::getPath());
    }

    /**
     * Bind providers to the application.
     *
     * This method binds service providers to the application, allowing them
     * to register services and perform any necessary setup.
     *
     * @return void
     */
    private function bindProviders(): void
    {
        $config = require Path::getPath('config', 'app.php');
        $providers = $config['providers'];

        foreach ($providers as $provider) {
            $instance = new $provider();
            if ($instance instanceof ServiceProviderInterface) {
                $instance->bind($this);
            }
        }
    }

    /**
     * Register the facades wth the application.
     *
     * @return void
     */
    private function registerFacades(): void
    {
        $config = require Path::getPath('config', 'app.php');
        $aliases = $config['facades'];

        AliasLoader::getInstance($aliases)->load();
    }

    /**
     * Dispatch the application.
     *
     * This method dispatches the application, including routing setup and
     * handling of HTTP requests.
     *
     * @return Response An instance of Response representing the application's response.
     * @throws Throwable If an error occurs during dispatching.
     */
    private function dispatch(): Response
    {
        $routes = require Path::getPath('routes', 'web.php');
        $routes(Router::class);

        $response = Router::dispatch();

        if (!$response instanceof Response) {
            /** @phpstan-ignore-next-line */
            $response = $this->resolve('response')->content($response);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): string
    {
        return static::VERSION;
    }

    /**
     * {@inheritdoc}
     */
    public function environment(string|array ...$environments): string|bool
    {
        if (count($environments) > 0) {
            $patterns = is_array($environments[0]) ? $environments[0] : $environments;

            return Str::is($patterns, $this->app['env']);
        }

        return $this->app['env'];
    }

    /**
     * Get the environment file the application is using.
     *
     * @return string Return the environment file the application using.
     */
    public function getEnvironmentFile(): string
    {
        return $this->environmentFile ?: '.env';
    }

    /**
     * Get the fully qualified path to the environment file.
     *
     * @return string Return the fully qualified path to the environment file.
     */
    public function getEnvironmentFilePath(): string
    {
        return $this->getEnvironmentPath() . DIRECTORY_SEPARATOR . $this->getEnvironmentFile();
    }

    /**
     * Get the path to the environment file directory.
     *
     * @return string Return the path to the environment file directory.
     */
    public function getEnvironmentPath(): string
    {
        return $this->environmentPath ?: $this->basePath;
    }

    /**
     * Set the environment file to be loading during bootstrapping.
     *
     * @param  string $file Holds the environment file to be loading during bootstrapping.
     * @return $this
     */
    public function setEnvironmentFile(string $file): self
    {
        $this->environmentFile = $file;

        return $this;
    }

    /**
     * Set the environment directory path.
     *
     * @param  string $path Holds the application path.
     * @return $this
     */
    public function setEnvironmentPath(string $path): self
    {
        $this->environmentPath = $path;

        return $this;
    }

    public function getEnvironment(): string
    {
        return env('APP_ENV');
    }

    public function isDebug(): bool
    {
        return env('APP_DEBUG');
    }

    /**
     * Detect the application's current environment.
     *
     * @param  Closure $callback
     * @return string
     */
    public function detectEnvironment(Closure $callback): string
    {
        $args = $_SERVER['argv'] ?? null;

        return $this->app['env'] = (new EnvironmentDetector())->detect($callback, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentTimeZone(): self
    {
        date_default_timezone_set(env('TIMEZONE'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentTimeZone(): string
    {
        return date('Y-m-d H:i:s', time());
    }
}
