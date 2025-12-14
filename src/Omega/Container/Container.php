<?php

/**
 * Part of Omega - Container Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Container;

use ArrayAccess;
use Closure;
use Omega\Container\Exceptions\AliasException;
use Omega\Container\Exceptions\BindingResolutionException;
use Omega\Container\Exceptions\CircularAliasException;
use Omega\Container\Exceptions\EntryNotFoundException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReturnTypeWillChange;

use function class_exists;
use function compact;
use function count;
use function end;
use function interface_exists;
use function is_object;
use function sprintf;

/**
 * A dependency injection container that manages bindings, singletons, aliases, and resolves dependencies.
 *
 * This container allows registering services, resolving classes with constructor injection,
 * calling callables with automatic dependency injection, and managing shared instances.
 *
 * @category  Omega
 * @package   Container
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class Container implements ArrayAccess
{
    /** @var array<string, array{concrete: Closure, shared: bool}> Container's bindings for abstract types */
    protected array $bindings = [];

    /** @var array<string, mixed> Container's shared instances (singleton cache) */
    protected array $instances = [];

    /** @var array<string, string> Registered aliases for abstract types */
    protected array $aliases = [];

    /** @var ?Resolver Dependency resolver instance for class instantiation */
    private ?Resolver $resolver = null;

    /** @var ?Invoker Callable invoker instance for automatic dependency injection */
    private ?Invoker $invoker = null;

    /** @var ?ReflectionCache Reflection cache instance to optimize repeated reflection calls */
    private ?ReflectionCache $reflectionCache = null;

    /** @var ?Injector Dependency injector instance to inject existing objects */
    private ?Injector $injector = null;

    /** @var list<array> Stack of parameter overrides for resolving dependencies */
    protected array $with = [];

    /**
     * Register a binding in the container.
     *
     * Binds an abstract type to a concrete implementation. If no concrete
     * is provided, the abstract is assumed to be concrete itself.
     *
     * @param string               $abstract The abstract identifier or class name.
     * @param Closure|string|null  $concrete The concrete implementation or factory.
     * @param bool                 $shared   Whether the binding should be shared (singleton).
     * @return void
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     */
    public function bind(string $abstract, Closure|string|null $concrete = null, bool $shared = false): void
    {
        $abstract = $this->getAlias($abstract);

        $concrete ??= $abstract;

        // If the concrete is not a Closure, we will make it one.
        if (false === $concrete instanceof Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Resolve and return an entry from the container.
     *
     * If the identifier is not bound but refers to an existing class or
     * interface, it will be resolved automatically.
     *
     * @param string $name The entry identifier or class name.
     * @return mixed The resolved instance or value.
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function get(string $name): mixed
    {
        if (false === $this->has($name)
            && false === class_exists($name)
            && false === interface_exists($name)
        ) {
            throw new EntryNotFoundException($name);
        }

        return $this->resolve($name);
    }

    /**
     * Resolve a new instance from the container.
     *
     * Unlike get(), this method always returns a new instance and bypasses
     * the shared instance cache.
     *
     * @param string|class-string      $name       The entry identifier or class name.
     * @param array<int|string, mixed> $parameters Parameters to override dependency resolution.
     * @return mixed The newly resolved instance.
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function make(string $name, array $parameters = []): mixed
    {
        return $this->resolve($name, $parameters, false);
    }

    /**
     * Define a value or object in the container.
     *
     * If a Closure is provided, it will be treated as a factory for a shared
     * instance. Otherwise, the value is stored as a resolved singleton.
     *
     * @param string $name  The entry identifier.
     * @param mixed  $value The value or factory to store.
     * @return void
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     */
    public function set(string $name, mixed $value): void
    {
        // If the value is a Closure,
        // it's a factory for a shared instance.
        if ($value instanceof Closure) {
            $this->bind($name, $value, true);

            return;
        }

        $name = $this->getAlias($name);

        // Store the value directly as a resolved.
        $this->instances[$name] = $value;
        $this->bindings[$name]  = [
            'concrete' => fn () => $this->instances[$name],
            'shared'   => true,
        ];
    }

    /**
     * Determine if the container can resolve the given identifier.
     *
     * An identifier is considered resolvable if it is bound, aliased,
     * or refers to an existing class or interface.
     *
     * @param string $id The entry identifier.
     * @return bool True if the identifier can be resolved, false otherwise.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     */
    public function has(string $id): bool
    {
        return $this->bound($id) || class_exists($id) || interface_exists($id);
    }

    /**
     * Register an alias for an abstract type.
     *
     * The alias will be resolved to the given abstract when requested.
     *
     * @param string $abstract The original abstract identifier.
     * @param string $alias    The alias name.
     * @return void
     * @throws AliasException Thrown when an alias maps to itself.
     */
    public function alias(string $abstract, string $alias): void
    {
        if ($abstract === $alias) {
            throw new AliasException($abstract);
        }

        $this->aliases[$alias] = $abstract;
    }

    /**
     * Resolve and return the final alias for an abstract type.
     *
     * If the abstract is aliased multiple times, all aliases are resolved
     * recursively until the original abstract is reached.
     *
     * @param string $abstract The abstract identifier.
     * @return string The resolved abstract identifier.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     */
    public function getAlias(string $abstract): string
    {
        return $this->resolveAlias($abstract, []);
    }

    /**
     * Resolve an alias to its final abstract name, following the alias chain.
     *
     * @param string $abstract  The abstract type or alias to resolve.
     * @param array<string, true> $resolving Map of aliases currently being resolved to detect cycles.
     * @return string The resolved abstract type.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     */
    private function resolveAlias(string $abstract, array $resolving): string
    {
        if (false === isset($this->aliases[$abstract])) {
            return $abstract;
        }

        if (isset($resolving[$abstract])) {
            throw new CircularAliasException($abstract);
        }

        $resolving[$abstract] = true;

        return $this->resolveAlias($this->aliases[$abstract], $resolving);
    }

    /**
     * Create the Closure used to resolve a concrete implementation.
     *
     * The returned Closure is responsible for building or resolving the concrete
     * type when the abstract is requested from the container.
     *
     * @param string $abstract The abstract type being bound.
     * @param string $concrete The concrete implementation.
     * @return Closure(self, array<int|string, mixed>): mixed
     */
    protected function getClosure(string $abstract, string $concrete): Closure
    {
        return function ($container, $parameters = []) use ($abstract, $concrete) {
            if ($abstract == $concrete) {
                return $container->build($concrete, $parameters);
            }

            return $container->resolve($concrete, $parameters, false);
        };
    }

    /**
     * Resolve the given abstract type from the container.
     *
     * This method handles alias resolution, shared instances, parameter overrides,
     * and delegates object creation to closures or the resolver.
     *
     * @param string $abstract The abstract type to resolve.
     * @param array<int|string, mixed> $parameters Parameters to override dependency resolution.
     * @param bool $useCache Whether to use cached shared instances.
     * @return mixed The resolved object or value.
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    protected function resolve(string $abstract, array $parameters = [], bool $useCache = true): mixed
    {
        $abstract = $this->getAlias($abstract);

        if ($useCache && isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $this->with[] = $parameters;

        $concrete = $this->getConcrete($abstract);

        if ($concrete instanceof Closure) {
            $object = $this->call($concrete, $this->getLastParameterOverride());
        } else {
            $object = $this->build($concrete, $this->getLastParameterOverride());
        }

        if ($useCache || $this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        array_pop($this->with);

        return $object;
    }

    /**
     * Determine whether the given abstract type is registered as shared.
     *
     * @param string $abstract The abstract type to check.
     * @return bool True if the binding is shared, false otherwise.
     */
    protected function isShared(string $abstract): bool
    {
        return isset($this->bindings[$abstract]['shared'])
            && true === $this->bindings[$abstract]['shared'];
    }

    /**
     * Get the concrete implementation for a given abstract type.
     *
     * If no binding exists, the abstract itself is returned.
     *
     * @param string $abstract The abstract type.
     * @return mixed The concrete implementation or the abstract itself.
     */
    protected function getConcrete(string $abstract): mixed
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Build a concrete instance.
     *
     * If the concrete is a Closure, it is executed directly.
     * Otherwise, the class is instantiated through the resolver.
     *
     * @param string|Closure $concrete The concrete class name or factory Closure.
     * @param array<int|string, mixed> $parameters Parameters to override dependency resolution.
     * @return mixed The instantiated object.
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function build(string|Closure $concrete, array $parameters = []): mixed
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        return $this->getResolver()->resolveClass($concrete, $parameters);
    }

    /**
     * Lazily create and return the dependency resolver.
     *
     * @return Resolver The resolver instance used to build concrete classes.
     */
    private function getResolver(): Resolver
    {
        if (null === $this->resolver) {
            $this->resolver = new Resolver($this);
        }

        return $this->resolver;
    }

    /**
     * Get a cached ReflectionClass instance for the given class or interface.
     *
     * @param string $class Fully-qualified class or interface name.
     * @return ReflectionClass<object> Reflection metadata for the given class.
     *
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function getReflectionClass(string $class): ReflectionClass
    {
        return $this->getReflectionCache()->getReflectionClass($class, function () use ($class) {
            if (false === class_exists($class) && false === interface_exists($class)) {
                throw new ReflectionException(
                    sprintf(
                        "Class %s does not exist",
                        $class
                    )
                );
            }

            return new ReflectionClass($class);
        });
    }

    /**
     * Get a cached ReflectionMethod instance for the given class and method.
     *
     * @param string|object $class Class name or object instance.
     * @param string        $method Method name to reflect.
     * @return ReflectionMethod Reflection metadata for the requested method.
     *
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function getReflectionMethod(string|object $class, string $method): ReflectionMethod
    {
        $className = is_object($class) ? $class::class : $class;

        return $this->getReflectionCache()->getReflectionMethod(
            $className,
            $method,
            fn () => new ReflectionMethod($class, $method)
        );
    }

    /**
     * Get the constructor parameters for the given class.
     *
     * @param string $class Fully-qualified class name.
     * @return ReflectionParameter[]|null List of constructor parameters,
     *                                   or null if the class has no constructor.
     *
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function getConstructorParameters(string $class): ?array
    {
        return $this->getReflectionCache()->getConstructorParameters($class, function () use ($class) {
            $reflector   = $this->getReflectionClass($class);
            $constructor = $reflector->getConstructor();

            return $constructor?->getParameters();
        });
    }

    /**
     * Lazily create and return the reflection cache instance.
     *
     * @return ReflectionCache The reflection cache used by the container.
     */
    private function getReflectionCache(): ReflectionCache
    {
        if (null === $this->reflectionCache) {
            $this->reflectionCache = new ReflectionCache();
        }

        return $this->reflectionCache;
    }

    /**
     * Determine whether the given type name represents a PHP primitive type.
     *
     * @param string $type Type name to check.
     * @return bool True if the type is a primitive, false otherwise.
     */
    protected function isPrimitiveType(string $type): bool
    {
        static $types = [
            'int'       => true,
            'float'     => true,
            'string'    => true,
            'bool'      => true,
            'array'     => true,
            'object'    => true,
            'callable'  => true,
            'iterable'  => true,
            'resource'  => true,
        ];

        return isset($types[$type]);
    }

    /**
     * Get the most recent parameter override set during resolution.
     *
     * @return array<int|string, mixed> Parameter overrides for the current resolution scope.
     */
    public function getLastParameterOverride(): array
    {
        return count($this->with) ? end($this->with) : [];
    }

    /**
     * Call a callable and automatically inject its dependencies.
     *
     * @param callable|object|array|string $callable   The callable to invoke.
     * @param array<int|string, mixed>     $parameters Optional parameters to override injection.
     * @return mixed The result returned by the callable.
     *
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function call(callable|object|array|string $callable, array $parameters = []): mixed
    {
        return $this->getInvoker()->call($callable, $parameters);
    }

    /**
     * Lazily create and return the callable invoker.
     *
     * @return Invoker The invoker responsible for calling callables with injection.
     */
    private function getInvoker(): Invoker
    {
        if (null === $this->invoker) {
            $this->invoker = new Invoker($this);
        }

        return $this->invoker;
    }

    /**
     * Inject dependencies into an existing object instance.
     *
     * @param object $instance The object on which dependencies should be injected.
     * @return object The same instance, after dependency injection.
     *
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function injectOn(object $instance): object
    {
        return $this->getInjector()->inject($instance);
    }

    /**
     * Determine whether the given abstract type is bound in the container.
     *
     * This method checks bindings, shared instances, and registered aliases
     * after resolving the abstract through the alias map.
     *
     * @param string $abstract The abstract type or identifier to check.
     * @return bool True if the type is bound or aliased, false otherwise.
     *
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     */
    public function bound(string $abstract): bool
    {
        $abstract = $this->getAlias($abstract);

        return isset($this->bindings[$abstract])
            || isset($this->instances[$abstract])
            || isset($this->aliases[$abstract]);
    }

    /**
     * Clear the internal reflection cache.
     *
     * This forces all cached reflection metadata (classes, methods,
     * constructors) to be rebuilt on the next resolution.
     *
     * @return $this The container instance for method chaining.
     */
    public function clearCache(): self
    {
        $this->getReflectionCache()->clear();

        return $this;
    }

    /**
     * Retrieve all registered container bindings.
     *
     * @return array<string, array{concrete: Closure, shared: bool}>
     *         An array of bindings indexed by abstract type.
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Remove all bindings, instances, aliases, and cached services.
     *
     * This resets the container to a clean state.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->bindings        = [];
        $this->instances       = [];
        $this->aliases         = [];
        $this->with            = [];
        $this->resolver        = null;
        $this->invoker         = null;
        $this->reflectionCache = null;
    }

    /**
     * Determine whether a container entry exists (ArrayAccess).
     *
     * @param mixed $offset The entry identifier.
     * @return bool True if the entry exists, false otherwise.
     *
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Retrieve an entry from the container (ArrayAccess).
     *
     * This is equivalent to calling {@see make()} on the given offset.
     *
     * @param string|class-string<mixed> $offset Entry name or class name.
     * @return mixed The resolved value.
     *
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->make($offset);
    }

    /**
     * Register a value or factory in the container (ArrayAccess).
     *
     * Non-closure values are automatically wrapped in a factory closure.
     *
     * @param mixed $offset The entry identifier.
     * @param mixed $value  The value or factory to bind.
     * @return void
     *
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->bind($offset, $value instanceof Closure ? $value : fn () => $value);
    }

    /**
     * Remove a binding or instance from the container (ArrayAccess).
     *
     * This also removes any aliases pointing to the same abstract.
     *
     * @param mixed $offset The entry identifier to remove.
     * @return void
     *
     * @throws CircularAliasException If a circular alias reference is detected.
     */
    public function offsetUnset(mixed $offset): void
    {
        $offset = $this->getAlias($offset);

        unset($this->instances[$offset]);
        unset($this->bindings[$offset]);

        foreach ($this->aliases as $alias => $abstract) {
            if ($abstract === $offset || $alias === $offset) {
                unset($this->aliases[$alias]);
            }
        }
    }

    /**
     * Lazily instantiate and return the injector instance.
     *
     * @return Injector The dependency injector.
     */
    private function getInjector(): Injector
    {
        if (null === $this->injector) {
            $this->injector = new Injector($this);
        }

        return $this->injector;
    }
}
