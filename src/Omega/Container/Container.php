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

use ArrayAccess;
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
class Container implements ArrayAccess, ContainerInterface
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
            throw new KeyNotFoundException(
                sprintf(
                    "The service '%s' is not registered in the container. Please check if it is correctly bound.",
                    $id
                )
            );
        }

        if (!isset($this->resolved[$id])) {
            $this->resolved[$id] = $this->bindings[$id]();
        }

        return $this->resolved[$id];
    }

    /**
     * Set a service or value in the container.
     *
     * @param string $id    The alias for the service.
     * @param mixed  $value The value or service to store.
     * @return void
     */
    public function set(string $id, mixed $value): void
    {
        $this->bindings[$id] = fn() => $value;
        $this->resolved[$id] = null;
    }

    /**
     * Unset a service from the container.
     *
     * @param string $id The alias of the service to unset.
     * @return void
     */
    public function unset(string $id): void
    {
        unset($this->bindings[$id], $this->resolved[$id]);
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
    public function instance(string $id, mixed $value): void
    {
        if (!$this->has($id)) {
            $this->set($id, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function definition(callable $setter): void
    {
        $services = $setter();

        if (is_array($services)) {
            foreach ($services as $id => $value) {
                $this->instance($id, $value);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $id): mixed
    {
        if (!isset($this->bindings[$id])) {
            throw new KeyNotFoundException(
                sprintf(
                    '%s is not bound to the container. Ensure that it has been registered before resolution.',
                    $id
                )
            );
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
                'The provided callable is not invocable. 
                Please ensure that the callback is correctly defined and accessible.'
            );
        }

        $reflector = $this->getReflector($callable);
        $dependencies = [];

        foreach ($reflector->getParameters() as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if (array_key_exists($name, $parameters)) {
                $dependencies[$name] = $parameters[$name];
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[$name] = $parameter->getDefaultValue();
                continue;
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                try {
                    $dependencies[$name] = $this->resolve($type->getName());
                    continue;
                } catch (DependencyResolutionException $e) {
                    throw new DependencyResolutionException(
                        sprintf("Unable to resolve the dependency '%s' of type '%s'.", $name, $type->getName()),
                        previous: $e
                    );
                }
            }

            throw new DependencyResolutionException(
                sprintf(
                    "Cannot resolve dependency '%s'. 
                    No default value is available, and no matching binding found in the container.",
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
            'Unsupported callable type provided. Only array and closure callables are supported.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->unset($offset);
    }
}
