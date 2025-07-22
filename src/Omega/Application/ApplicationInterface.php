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
use Omega\Container\Provider\AbstractServiceProvider;
use Omega\Http\Exceptions\HttpException;

/**
 * Core application interface.
 *
 * This interface defines the essential contract for interacting with the application container,
 * including service providers registration, configuration loading, environment detection,
 * bootstrapping lifecycle, and application termination.
 *
 * It also provides container accessors for application paths, modes, and state.
 *
 * @category  Omega
 * @package   Application
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
interface ApplicationInterface
{
    /**
     * Loads and applies configuration to the application.
     *
     * @param ConfigRepository $configs The configuration repository instance.
     * @return void
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a service or value is not found in the container.
     */
    public function loadConfig(ConfigRepository $configs): void;

    /**
     * Returns the default configuration array for fallback purposes.
     *
     * @return array<string, mixed> The array of default configuration values.
     */
    public function defaultConfigs(): array;

    /**
     * Sets the base path.
     *
     * This method assigns the given path to the base directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the base directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setBasePath(string $path): self;

    /**
     * Sets the app path.
     *
     * This method assigns the given path to the app directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the app directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setAppPath(string $path): self;

    /**
     * Sets the model path.
     *
     * This method assigns the given path to the model directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the model directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setModelPath(string $path): self;

    /**
     * Sets the view path.
     *
     * This method assigns the given path to the view directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the view directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setViewPath(string $path): self;

    /**
     * Sets the view paths.
     *
     * This method assigns the given array of paths for the view resource type (e.g., views, translations),
     * and stores them into the container for later resolution and usage by the application.
     *
     * @param string[] $paths A list of absolute paths to the view directories.
     * @return self Returns the current instance for method chaining.
     */
    public function setViewPaths(array $paths): self;

    /**
     * Sets the controller path.
     *
     * This method assigns the given path to the controller directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the controller directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setControllerPath(string $path): self;

    /**
     * Sets the services path.
     *
     * This method assigns the given path to the services directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the services' directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setServicesPath(string $path): self;

    /**
     * Sets the component path.
     *
     * This method assigns the given path to the component directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the component directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setComponentPath(string $path): self;

    /**
     * Sets the command path.
     *
     * This method assigns the given path to the command directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the command directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setCommandPath(string $path): self;

    /**
     * Sets the storage path.
     *
     * This method assigns the given path to the storage directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the storage directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setStoragePath(string $path): self;

    /**
     * Sets the cache path.
     *
     * This method assigns the given path to the cache directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the cache directory.
     * @return self Returns the current instance for method chaining.
     *
     * @deprecated version 0.32 use compiled_view_path sited.
     */
    public function setCachePath(string $path): self;

    /**
     * Sets the compiled path.
     *
     * This method assigns the given path to the compiled directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the compiled directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setCompiledViewPath(string $path): self;

    /**
     * Sets the config path.
     *
     * This method assigns the given path to the config directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the config directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setConfigPath(string $path): self;

    /**
     * Sets the middleware path.
     *
     * This method assigns the given path to the middleware directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the middleware directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setMiddlewarePath(string $path): self;

    /**
     * Sets the provider path.
     *
     * This method assigns the given path to the provider directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the provider directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setProviderPath(string $path): self;

    /**
     * Sets the migration path.
     *
     * This method assigns the given path to the migration directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the migration directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setMigrationPath(string $path): self;

    /**
     * Sets the seeder path.
     *
     * This method assigns the given path to the seeder directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the seeder directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setSeederPath(string $path): self;

    /**
     * Sets the public path.
     *
     * This method assigns the given path to the public directory (e.g., "base", "config", "modules"),
     * and stores it into the container under a corresponding key for later retrieval.
     *
     * @param string $path The absolute path to the public directory.
     * @return self Returns the current instance for method chaining.
     */
    public function setPublicPath(string $path): self;

    /**
     * Returns the base path.
     *
     * Retrieves the absolute path to the base directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the base directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getBasePath(): string;

    /**
     * Returns the app path.
     *
     * Retrieves the absolute path to the app directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the app directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getAppPath(): string;

    /**
     * Returns the application cache path.
     *
     * Retrieves the absolute path to the application cache directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the application cache directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getApplicationCachePath(): string;

    /**
     * Returns the module path.
     *
     * Retrieves the absolute path to the module directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the module directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getModelPath(): string;

    /**
     * Returns the view path.
     *
     * Retrieves the absolute path to the view directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the view directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getViewPath(): string;

    /**
     * Returns the view paths.
     *
     * Retrieves an array of absolute paths where the application should look for view templates.
     * These paths are typically defined via the corresponding setter and stored in the container.
     *
     * @return string[] List of absolute paths to view directories.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getViewPaths(): array;

    /**
     * Returns the controller path.
     *
     * Retrieves the absolute path to the controller directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the controller directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getControllerPath(): string;

    /**
     * Returns the services path.
     *
     * Retrieves the absolute path to the services directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the services' directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getServicesPath(): string;

    /**
     * Returns the component path.
     *
     * Retrieves the absolute path to the component directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the component directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getComponentPath(): string;

    /**
     * Returns the command path.
     *
     * Retrieves the absolute path to the command directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the command directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getCommandPath(): string;

    /**
     * Returns the storage path.
     *
     * Retrieves the absolute path to the storage directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the storage directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getStoragePath(): string;

    /**
     * Returns the cache path.
     *
     * Retrieves the absolute path to the cache directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the cache directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     *
     * @deprecated version 0.32 use compiled_view_path instead.
     */
    public function getCachePath(): string;

    /**
     * Returns the compiled path.
     *
     * Retrieves the absolute path to the compiled directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the compiled directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getCompiledViewPath(): string;

    /**
     * Returns the config path.
     *
     * Retrieves the absolute path to the config directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the config directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getConfigPath(): string;

    /**
     * Returns the middleware path.
     *
     * Retrieves the absolute path to the middleware directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the middleware directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getMiddlewarePath(): string;

    /**
     * Returns the provider path.
     *
     * Retrieves the absolute path to the provider directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the provider directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getProviderPath(): string;

    /**
     * Returns the migration path.
     *
     * Retrieves the absolute path to the migration directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the migration directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getMigrationPath(): string;

    /**
     * Returns the seeder path.
     *
     * Retrieves the absolute path to the seeder directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the seeder directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getSeederPath(): string;

    /**
     * Returns the public path.
     *
     * Retrieves the absolute path to the public directory (e.g., base, config, routes),
     * previously registered in the container.
     *
     * @return string The path to the public directory.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    public function getPublicPath(): string;

    /**
     * Returns the current application environment (e.g., "dev", "prod", "testing").
     *
     * @return string The current environment name.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a service or value is not found in the container.
     */
    public function getEnvironment(): string;

    /**
     * Checks whether the application is running in debug mode.
     *
     * @return bool True if debug mode is enabled, false otherwise.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a service or value is not found in the container.
     */
    public function isDebugMode(): bool;

    /**
     * Checks whether the application is running in production mode.
     *
     * @return bool True if in production mode, false otherwise.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a service or value is not found in the container.
     */
    public function isProduction(): bool;

    /**
     * Checks whether the application is running in development mode.
     *
     * @return bool True if in development mode, false otherwise.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a service or value is not found in the container.
     */
    public function isDev(): bool;

    /**
     * Indicates whether the application has completed the boot process.
     *
     * @return bool True if booted, false otherwise.
     */
    public function isBooted(): bool;

    /**
     * Indicates whether the application has completed the bootstrap process.
     *
     * @return bool True if bootstrapped, false otherwise.
     */
    public function isBootstrapped(): bool;

    /**
     * Bootstraps the application with the given service providers.
     *
     * @param array<int, class-string> $providers The service provider class names.
     * @return void
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a service or value is not found in the container.
     */
    public function bootstrapWith(array $providers): void;

    /**
     * Boots all registered service providers.
     *
     * @return void
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a service or value is not found in the container.
     */
    public function bootProvider(): void;

    /**
     * Registers all configured service providers.
     *
     * @return void
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a service or value is not found in the container.
     */
    public function registerProvider(): void;

    /**
     * Calls the provided boot callbacks after booting.
     *
     * @param array<int, callable> $bootCallBacks The list of callbacks to execute.
     * @return void
     */
    public function callBootCallbacks(array $bootCallBacks): void;

    /**
     * Adds a booting callback to be called before the application boots.
     *
     * @param callable $callback The booting callback.
     * @return void
     */
    public function bootingCallback(callable $callback): void;

    /**
     * Adds a booted callback to be called after the application has booted.
     *
     * @param callable $callback The booted callback.
     * @return void
     */
    public function bootedCallback(callable $callback): void;

    /**
     * Flushes or resets the entire application state.
     *
     * @return void
     */
    public function flush(): void;

    /**
     * Registers a service provider by class name.
     *
     * @param string $provider The fully-qualified class name of the provider.
     * @return AbstractServiceProvider The registered provider instance.
     */
    public function register(string $provider): AbstractServiceProvider;

    /**
     * Registers a terminating callback to be called on application shutdown.
     *
     * @param callable $terminateCallback The callback to execute on termination.
     * @return Application The current application instance.
     */
    public function registerTerminate(callable $terminateCallback): Application;

    /**
     * Terminates the application by executing registered callbacks.
     *
     * @return void
     */
    public function terminate(): void;

    /**
     * Checks if the application is currently in maintenance mode.
     *
     * @return bool True if in maintenance mode, false otherwise.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a service or value is not found in the container.
     */
    public function isDownMaintenanceMode(): bool;

    /**
     * Returns the maintenance mode configuration data.
     *
     * @return array<string, string|int|null> The down file contents as an associative array.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a service or value is not found in the container.
     */
    public function getDownData(): array;

    /**
     * Aborts the application and throws an HTTP exception.
     *
     * @param int $code The HTTP status code.
     * @param string $message The error message.
     * @param array<string, string> $headers Optional headers to include.
     * @return void
     * @throws HttpException Always throws.
     */
    public function abort(int $code, string $message = '', array $headers = []): void;
}
