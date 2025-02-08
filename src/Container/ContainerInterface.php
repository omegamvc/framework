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
use Omega\Container\Exception\DependencyResolutionException;
use Omege\Container\Exception\InvalidCallableException;
use Omega\Container\Exception\KeyNotFoundException;
use Psr\Container\ContainerInterface as PsrContainerInterface;

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
interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Bind the class.
     *
     * @param string   $id      Holds the class alias or key.
     * @param callable $factory Holds a closure that defines how to create the class instance.
     * @return $this
     */
    public function alias(string $id, callable $factory): static;

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
