<?php

/**
 * Part of Omega - Facades Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\Application\Application;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Support\Facades\Exceptions\FacadeObjectNotSetException;

use function array_key_exists;

/**
 * Base class for implementing facades.
 *
 * Facades provide a static interface to underlying objects managed by the
 * application container. This class handles resolving and caching the actual
 * instance behind the facade, as well as forwarding static calls to that instance.
 *
 * @category   Omega
 * @package    Support
 * @subpackage Facades
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
abstract class AbstractFacade implements FacadeInterface
{
    /** @var Application|null The Application container instance. */
    protected static ?Application $app = null;

    /** @var array<string, mixed> Array mapping accessor names to resolved instances */
    protected static array $instance = [];

    /**
     * Create a new facade instance and register the application container.
     *
     * @param Application $app The application container instance
     * @return void
     */
    public function __construct(Application $app)
    {
        static::$app = $app;
    }

    /**
     * Set the application container to be used by all facades.
     *
     * @param Application|null $app The application container, or null to unset
     * @return void
     */
    public static function setFacadeBase(?Application $app = null): void
    {
        static::$app = $app;
    }

    /**
     * Get the container binding key for the underlying instance.
     *
     * @return string The service identifier used to resolve the instance
     */
    abstract public static function getFacadeAccessor(): string;

    /**
     * Resolve and retrieve the underlying facade instance.
     *
     * @return mixed The resolved instance
     * @throws DependencyException If a dependency cannot be resolved
     * @throws InvalidDefinitionException If the container definition is invalid
     * @throws NotFoundException If the service cannot be found in the container
     */
    protected static function getFacade(): mixed
    {
        return static::getFacadeBase(static::getFacadeAccessor());
    }

    /**
     * Resolve a facade instance by name or class.
     *
     * @param string|class-string $name Entry name or class name to resolve
     * @return mixed The resolved instance
     * @throws DependencyException If a dependency cannot be resolved
     * @throws InvalidDefinitionException If the container definition is invalid
     * @throws NotFoundException If the service cannot be found in the container
     */
    protected static function getFacadeBase(string $name): mixed
    {
        if (array_key_exists($name, static::$instance)) {
            return static::$instance[$name];
        }

        return static::$instance[$name] = static::$app->make($name);
    }

    /**
     * Clear all cached resolved facade instances.
     *
     * @return void
     */
    public static function flushInstance(): void
    {
        static::$instance = [];
    }

    /**
     * Forward static method calls to the underlying instance.
     *
     * @param string $name The method name being called
     * @param array<int, mixed> $arguments The method arguments
     * @return mixed The method return value
     * @throws FacadeObjectNotSetException If no underlying instance is available
     * @throws DependencyException If a dependency cannot be resolved
     * @throws InvalidDefinitionException If the container definition is invalid
     * @throws NotFoundException If the service is not found in the container
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        $instance = static::getFacade();

        if (!$instance) {
            throw new FacadeObjectNotSetException(
                static::class
            );
        }

        return $instance->$name(...$arguments);
    }
}
