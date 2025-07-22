<?php

/**
 * Part of Omega - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Facades;

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Application\Application;
use RuntimeException;

use function array_key_exists;

/**
 * Abstract base class for Facades.
 *
 * Provides a static interface to services registered in the Application container.
 * Acts as a proxy to resolve and call methods on the underlying service instances.
 *
 * @category   Omega
 * @package    Support
 * @subpackage Facades
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
abstract class Facade
{
    /**
     * The Application instance.
     *
     * @var Application|null The application container instance used to resolve services.
     */
    protected static ?Application $app = null;

    /**
     * Cached instances of resolved services.
     *
     * @var array<string, mixed> Array mapping service keys to their resolved instances.
     */
    protected static array $instance = [];

    /**
     * Constructor.
     *
     * Sets the application container instance.
     *
     * @param Application $app The application container instance.
     */
    public function __construct(Application $app)
    {
        static::$app = $app;
    }

    /**
     * Set the application container instance.
     *
     * Can be called statically to set or reset the application container.
     *
     * @param Application|null $app The application container instance or null to unset.
     * @return void
     */
    public static function setFacadeBase(?Application $app = null): void
    {
        static::$app = $app;
    }

    /**
     * Get the accessor key used to resolve the underlying service from the container.
     *
     * Subclasses must override this method to provide the service identifier.
     *
     * @return string The service accessor key or class name.
     * @throws RuntimeException If not implemented or application not available.
     */
    protected static function getAccessor(): string
    {
        throw new RuntimeException('Application not found');
    }

    /**
     * Resolve the underlying service instance from the container using the accessor.
     *
     * @return mixed The resolved service instance.
     * @throws DependencyException If the service cannot be resolved due to dependencies.
     * @throws NotFoundException If the service is not found in the container.
     */
    protected static function getFacade(): mixed
    {
        return static::getFacadeBase(static::getAccessor());
    }

    /**
     * Resolve the service instance by key from the container or cache.
     *
     * @param string $name Service key or class name.
     * @return mixed The resolved service instance.
     * @throws DependencyException If the service cannot be resolved.
     * @throws NotFoundException If the service is not found.
     */
    protected static function getFacadeBase(string $name): mixed
    {
        if (array_key_exists($name, static::$instance)) {
            return static::$instance[$name];
        }

        return static::$instance[$name] = static::$app->make($name);
    }

    /**
     * Clear all cached instances.
     *
     * Use this to reset the facade's resolved instances cache.
     *
     * @return void
     */
    public static function flushInstance(): void
    {
        static::$instance = [];
    }

    /**
     * Handle dynamic, static method calls to the facade.
     *
     * Resolves the underlying instance and calls the given method with provided arguments.
     *
     * @param string $name The method name being called statically.
     * @param array<int, mixed> $arguments Arguments passed to the method call.
     * @return mixed The result of the called method.
     * @throws DependencyException If the underlying service cannot be resolved.
     * @throws NotFoundException If the service or method is not found.
     * @throws RuntimeException If the facade root instance is not set.
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        $instance = static::getFacade();

        if (!$instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance->$name(...$arguments);
    }
}
