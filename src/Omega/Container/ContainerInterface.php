<?php

/**
 * Part of Omega - Container Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Container;

use ReflectionException;
use Omega\Container\Exception\ContainerExceptionInterface;
use Omega\Container\Exception\DependencyResolutionException;
use Omega\Container\Exception\InvalidCallableException;
use Omega\Container\Exception\KeyNotFoundException;
use Omega\Container\Exception\NotFoundExceptionInterface;

/**
 * Interface for a dependency injection container.
 *
 * The `Container` interface provides a contract for managing class instances and their dependencies.
 * It allows for binding classes to specific aliases, resolving dependencies, and retrieving instances.
 *
 * This interface extends the PSR-11 ContainerInterface and adds additional methods for enhanced
 * functionality, including the ability to remove bindings, clear all bindings, and retrieve
 * the current set of bindings.
 *
 * Implementations of this interface should ensure proper handling of dependency resolution,
 * enabling efficient and flexible management of service instances within an application.
 *
 * @category  Omega
 * @package   Container
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */
interface ContainerInterface
{
    /**
     * Check if a service or value is registered in the container.
     *
     * This method checks whether a given service ID exists in the container
     * and whether it has been registered with a factory.
     *
     * @param string $id The alias for the service to check.
     * @return bool True if the service exists, false otherwise.
     *
     * ```php
     * $container->has('database.connection');  // Returns true if the 'database.connection' is bound.
     * ```
     */
    public function has(string $id): bool;

    /**
     * Retrieve a service or value from the container.
     *
     * This method fetches the service associated with the given ID, resolving it
     * if necessary. If the service does not exist, a KeyNotFoundException is thrown.
     *
     * @param string $id The alias for the service to retrieve.
     * @return mixed The resolved service or value.
     * @throws KeyNotFoundException If the service is not found.
     *
     * ```php
     * $database = $container->get('database.connection');  // Retrieves the 'database.connection' service.
     * ```
     */
    public function get(string $id): mixed;

    /**
     * Bind a value to the container.
     *
     * This method directly binds a value (not a factory) to the container
     * under the specified alias. The value is immediately available for retrieval.
     *
     * @param string $id    The alias for the service.
     * @param mixed  $value The value or service to store.
     * @return void
     *
     * ```php
     * $container->set('app.name', 'OmegaApp');  // Binds the value 'OmegaApp' to the 'app.name' alias.
     * ```
     */
    public function set(string $id, mixed $value): void;

    /**
     * Unset a service or value from the container.
     *
     * This method removes a service or value from the container, so it will no longer
     * be available for retrieval.
     *
     * @param string $id The alias of the service to unset.
     * @return void
     *
     * ```php
     * $container->unset('app.name');  // Removes the 'app.name' service from the container.
     * ```
     */
    public function unset(string $id): void;

    /**
     * Bind a factory (callable) to the container.
     *
     * This method binds a callable (factory) that can be invoked to generate an instance
     * when the service is retrieved. The factory is invoked when the service is first accessed.
     *
     * @param string   $id     The alias for the service.
     * @param callable $factory A callable that returns the service instance.
     * @return static The current instance for method chaining.
     *
     * ```php
     * $container->alias('database.connection', function() {
     *     return new DatabaseConnection('localhost', 'root', 'password');
     * });
     * // The 'database.connection' alias will return an instance of DatabaseConnection when accessed.
     * ```
     */
    public function alias(string $id, callable $factory): static;

    /**
     * Bind an instance to the container if not already bound.
     *
     * This method binds a value or object to the container only if the service is not
     * already registered. It ensures that the value is only set once.
     *
     * @param string $id    The alias for the service.
     * @param mixed  $value The value or instance to store.
     * @return void
     *
     * ```php
     * $container->instance('path.base', '/var/www');
     * // Binds the value '/var/www' to 'path.base' only if it's not already set.
     * ```
     */
    public function instance(string $id, mixed $value): void;

    /**
     * Define multiple services using a callable setter.
     *
     * This method registers multiple services in the container by invoking a callable setter,
     * which is executed immediately to resolve the services and bind them to the container.
     * The setter should return an array where keys are the service aliases and values are
     * the corresponding values or services to bind.
     *
     * @param callable $setter A callable that returns an associative array of services.
     * @return void
     *
     * ```php
     * $container->definition(function() {
     *     return [
     *         'path.base' => '/var/www',
     *         'path.lang' => '/var/www/lang',
     *     ];
     * });
     * $basePath = $container->get('path.base');
     * $langPath = $container->get('path.lang');
     * ```
     */
    public function definition(callable $setter): void;

    /**
     * Resolve the container.
     *
     * @param string $id Holds the class alias or key.
     * @return mixed Return the resolved class instance.
     * @throws KeyNotFoundException if the key is not bound.
     */
    public function resolve(string $id): mixed;

    /**
     * Call a callable with dependency injection.
     *
     * @param callable|array{0: object|string, 1: string} $callable   Holds the callable function or method.
     * @param array<string, mixed>                        $parameters Holds an associative array of additional
     *                                                                parameters to pass.
     * @return mixed Return the result of the callable.
     * @throws ReflectionException           if the callable cannot be reflected.
     * @throws DependencyResolutionException if a dependency cannot be resolved.
     * @throws InvalidCallableException      if the callable is not invocable.
     */
    public function call(callable|array $callable, array $parameters = []): mixed;

    /**
     * Remove the binding for the given alias.
     *
     * @param string $id The class alias or key to remove.
     * @return bool Returns true if the alias was found and removed, false otherwise.
     */
    public function remove(string $id): bool;

    /**
     * Clear all bindings from the container.
     *
     * This method removes all registered aliases and resolved instances.
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Get all bindings from the container.
     *
     * @return array<string, callable> Returns an array of all registered bindings.
     */
    public function getBindings(): array;
}
