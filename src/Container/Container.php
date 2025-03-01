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
use ReflectionMethod;
use ReflectionNamedType;
use Omega\Container\Exception\DependencyResolutionException;
use Omega\Container\Exception\InvalidCallableException;
use Omega\Container\Exception\KeyNotFoundException;

use function array_values;
use function call_user_func;
use function is_array;
use function is_callable;
use function sprintf;
/**
 * Container class.
 *
 * The `Container` class provides a basic dependency injection container for
 * managing class instances and their dependencies.
 *
 * @category  Omega
 * @package   Container
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */
class Container implements ContainerInterface
{
    /**
     * Binding class.
     *
     * @var array<string, callable> Holds an array of class alias to factory closure bindings.
     */
    private array $bindings = [];

    /**
     * Resolved class.
     *
     * @var array<string, mixed> Holds an array of resolved class instances by alias.
     */
    private array $resolved = [];

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id): mixed
    {
        if (!isset($this->bindings[$id])) {
            throw new KeyNotFoundException(sprintf('Alias %s is not found in the container.', $id));
        }

        return $this->resolve($id);
    }

    /**
     * {@inheritdoc}
     */
    public function alias(string $id, callable $factory): static
    {
        $this->bindings[$id] = $factory;
        $this->resolved[$id] = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $id): mixed
    {
        if (!isset($this->bindings[$id])) {
            throw new KeyNotFoundException(sprintf('%d is not bound.', $id));
        }

        if (!isset($this->resolved[$id])) {
            $this->resolved[$id] = call_user_func($this->bindings[$id], $this);
        }

        return $this->resolved[$id];
    }

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
    public function call(callable|array $callable, array $parameters = []): mixed
    {
        if (!is_callable($callable)) {
            throw new InvalidCallableException(
                'The provided callable is not invocable.'
            );
        }

        $reflector = $this->getReflector($callable);
        $dependencies = [];

        foreach ($reflector->getParameters() as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            // Se il parametro è già fornito manualmente, lo usiamo
            if (array_key_exists($name, $parameters)) {
                $dependencies[$name] = $parameters[$name];
                continue;
            }

            // Se il parametro ha un valore di default, lo usiamo
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[$name] = $parameter->getDefaultValue();
                continue;
            }

            // Se ha un type hint, proviamo a risolverlo
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                try {
                    $dependencies[$name] = $this->resolve($type->getName());
                    continue;
                } catch (DependencyResolutionException $e) {
                    throw new DependencyResolutionException(
                        "Cannot resolve dependency '{$name}' of type '{$type->getName()}'.",
                        previous: $e
                    );
                }
            }

            // Se arriviamo qui, il parametro non può essere risolto
            throw new DependencyResolutionException(
                sprintf(
                    "Cannot resolve dependency %d, no default value available and no matching binding.", 
                    $name
                )
            );
        }

        return $callable(...array_values($dependencies));
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $id): bool
    {
        if (isset($this->bindings[$id])) {
            unset($this->bindings[$id], $this->resolved[$id]);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->bindings = [];
        $this->resolved = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Get reflector for the given callable.
     *
     * @param callable|array{0: object|string, 1: string} $callable Holds the callable function or method.
     * @return ReflectionMethod Return the reflection object for the callable.
     * @throws ReflectionException      if the callable cannot be reflected.
     * @throws InvalidCallableException if an unsupported callable type is provided.
     */
    private function getReflector(callable|array $callable): ReflectionMethod
    {
        if (is_array($callable)) {
            return new ReflectionMethod(is_object($callable[0]) ? get_class($callable[0]) : $callable[0], $callable[1]);
        }

        throw new InvalidCallableException(
            'Unsupported callable type provided.'
        );
    }
}
