<?php

declare(strict_types=1);

namespace Omega\Container;

use ArrayAccess;
use Closure;
use Exception;
use InvalidArgumentException;
use Omega\Container\Definition\Definition;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Definition\FactoryDefinition;
use Omega\Container\Definition\Helper\DefinitionHelperInterface;
use Omega\Container\Definition\InstanceDefinition;
use Omega\Container\Definition\ObjectDefinition;
use Omega\Container\Definition\Resolver\DefinitionResolverInterface;
use Omega\Container\Definition\Resolver\ResolverDispatcher;
use Omega\Container\Definition\Source\DefinitionArray;
use Omega\Container\Definition\Source\MutableDefinitionSource;
use Omega\Container\Definition\Source\ReflectionBasedAutowiring;
use Omega\Container\Definition\Source\SourceChain;
use Omega\Container\Definition\ValueDefinition;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Container\Invoker\DefinitionParameterResolver;
use Omega\Container\Invoker\Exception\InvocationException;
use Omega\Container\Invoker\Exception\NotCallableException;
use Omega\Container\Invoker\Exception\NotEnoughParametersException;
use Omega\Container\Invoker\Invoker;
use Omega\Container\Invoker\InvokerInterface;
use Omega\Container\Invoker\ParameterResolver\AssociativeArrayResolver;
use Omega\Container\Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Omega\Container\Invoker\ParameterResolver\DefaultValueResolver;
use Omega\Container\Invoker\ParameterResolver\NumericArrayResolver;
use Omega\Container\Invoker\ParameterResolver\ResolverChain;
use Omega\Container\Proxy\NativeProxyFactory;
use Omega\Container\Proxy\ProxyFactory;
use Omega\Container\Proxy\ProxyFactoryInterface;
use ReturnTypeWillChange;

use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_unique;
use function gettype;
use function implode;
use function is_array;
use function is_bool;
use function is_object;
use function is_scalar;
use function is_string;
use function preg_replace;
use function sort;
use function sprintf;
use function str_contains;
use function ucfirst;
use function var_export;

use const PHP_VERSION_ID;

/**
 * Dependency Injection Container.
 *
 * @implements ArrayAccess<string|class-string<mixed>, mixed>
 */
class Container implements ContainerInterface, FactoryInterface, InvokerInterface, ArrayAccess
{
    /** @var array Map of entries that are already resolved. */
    protected array $resolvedEntries = [];

    /** @var array|MutableDefinitionSource|SourceChain */
    private MutableDefinitionSource|array|SourceChain $definitionSource;

    /** @var DefinitionResolverInterface|ResolverDispatcher */
    private DefinitionResolverInterface|ResolverDispatcher $definitionResolver;

    /**  @var array<Definition|null> Map of definitions that are already fetched (local cache). */
    private array $fetchedDefinitions = [];

    /** @var array Array of entries being resolved. Used to avoid circular dependencies and infinite loops. */
    protected array $entriesBeingResolved = [];

    /** @var InvokerInterface|null  */
    private ?InvokerInterface $invoker = null;

    /** @var ContainerInterface Container that wraps this container. If none, points to $this. */
    protected ContainerInterface $delegateContainer;

    /** @var NativeProxyFactory|ProxyFactory|ProxyFactoryInterface|null */
    protected ProxyFactoryInterface|null|ProxyFactory|NativeProxyFactory $proxyFactory;

    /** @var array<string, string> Register aliases entry container. */
    protected array $aliases = [];

    /**
     * @param array $definitions
     * @return static
     * @throws Exception
     */
    public static function create(array $definitions) : static
    {
        $source = new SourceChain([new ReflectionBasedAutowiring]);
        $source->setMutableDefinitionSource(new DefinitionArray($definitions, new ReflectionBasedAutowiring));

        return new static($definitions);
    }

    /**
     * Use `$container = new Container()` if you want a container with the default configuration.
     *
     * If you want to customize the container's behavior, you are discouraged to create and pass the
     * dependencies yourself, the ContainerBuilder class is here to help you instead.
     *
     * @see ContainerBuilder
     *
     * @param array|MutableDefinitionSource $definitions
     * @param ProxyFactoryInterface|null    $proxyFactory
     * @param ContainerInterface|null       $wrapperContainer If the container is wrapped by another container.
     * @return void
     * @throws Exception
     */
    public function __construct(
        array|MutableDefinitionSource $definitions = [],
        ?ProxyFactoryInterface $proxyFactory = null,
        ?ContainerInterface $wrapperContainer = null,
    ) {
        if (is_array($definitions)) {
            $this->definitionSource = $this->createDefaultDefinitionSource($definitions);
        } else {
            $this->definitionSource = $definitions;
        }

        $this->delegateContainer = $wrapperContainer ?: $this;

        if ($proxyFactory === null) {
            $proxyFactory = (PHP_VERSION_ID >= 80400) ? new NativeProxyFactory : new ProxyFactory;
        }

        $this->proxyFactory = $proxyFactory;
        $this->definitionResolver = new ResolverDispatcher($this->delegateContainer, $this->proxyFactory);

        // Auto-register the container
        $this->resolvedEntries = [
            self::class => $this,
            ContainerInterface::class => $this->delegateContainer,
            FactoryInterface::class => $this,
            InvokerInterface::class => $this,
        ];
    }

    /**
     * Returns an entry of the container by its name.
     *
     * @template T
     * @param string|class-string<T> $id Entry name or a class name.
     * @return mixed|T
     * @throws DependencyException Error while resolving the entry.
     * @throws InvalidDefinitionException
     * @throws NotFoundException No entry found for the given name.
     */
    public function get(string $id) : mixed
    {
        $id = $this->getALias($id);

        // If the entry is already resolved we return it
        if (isset($this->resolvedEntries[$id]) || array_key_exists($id, $this->resolvedEntries)) {
            return $this->resolvedEntries[$id];
        }

        $definition = $this->getDefinition($id);
        if (! $definition) {
            throw new NotFoundException(sprintf("No entry or class found for '%s'", $id));
        }

        $value = $this->resolveDefinition($definition);

        $this->resolvedEntries[$id] = $value;

        return $value;
    }

    /**
     * @param string $name
     * @return Definition|null
     * @throws InvalidDefinitionException
     */
    private function getDefinition(string $name) : ?Definition
    {
        // Local cache that avoids fetching the same definition twice
        if (!array_key_exists($name, $this->fetchedDefinitions)) {
            $this->fetchedDefinitions[$name] = $this->definitionSource->getDefinition($name);
        }

        return $this->fetchedDefinitions[$name];
    }

    /**
     * Build an entry of the container by its name.
     *
     * This method behave like get() except resolves the entry again every time.
     * For example if the entry is a class then a new instance will be created each time.
     *
     * This method makes the container behave like a factory.
     *
     * @template T
     * @param string|class-string<T> $name       Entry name or a class name.
     * @param array                  $parameters Optional parameters to use to build the entry. Use this to force
     *                                           specific parameters to specific values. Parameters not defined in this
     *                                           array will be resolved using the container.
     *
     * @return mixed|T
     * @throws InvalidArgumentException The name parameter must be of type string.
     * @throws DependencyException Error while resolving the entry.
     * @throws InvalidDefinitionException
     * @throws NotFoundException No entry found for the given name.
     */
    public function make(string $name, array $parameters = []) : mixed
    {
        $name = $this->getAlias($name);

        $definition = $this->getDefinition($name);
        if (! $definition) {
            // If the entry is already resolved we return it
            if (array_key_exists($name, $this->resolvedEntries)) {
                return $this->resolvedEntries[$name];
            }

            throw new NotFoundException(sprintf("No entry or class found for '%s'", $name));
        }

        return $this->resolveDefinition($definition, $parameters);
    }

    /**
     * @param string $id
     * @return bool
     * @throws InvalidDefinitionException
     */
    public function has(string $id) : bool
    {
        $id = $this->getAlias($id);

        if (array_key_exists($id, $this->resolvedEntries)) {
            return true;
        }

        $definition = $this->getDefinition($id);
        if ($definition === null) {
            return false;
        }

        return $this->definitionResolver->isResolvable($definition);
    }

    /**
     * Inject all dependencies on an existing instance.
     *
     * @template T
     * @param object|T $instance Object to perform injection upon
     * @return object|T $instance Returns the same instance
     * @throws InvalidArgumentException
     * @throws InvalidDefinitionException
     */
    public function injectOn(object $instance) : object
    {
        $className = $instance::class;

        // If the class is anonymous, don't cache its definition
        // Checking for anonymous classes is cleaner via Reflection, but also slower
        $objectDefinition = str_contains($className, '@anonymous')
            ? $this->definitionSource->getDefinition($className)
            : $this->getDefinition($className);

        if (! $objectDefinition instanceof ObjectDefinition) {
            return $instance;
        }

        $definition = new InstanceDefinition($instance, $objectDefinition);

        $this->definitionResolver->resolve($definition);

        return $instance;
    }

    /**
     * Call the given function using the given parameters.
     *
     * Missing parameters will be resolved from the container.
     *
     * @param callable|array|string $callable Function to call.
     * @param array    $parameters Parameters to use. Can be indexed by the parameter names
     *                             or not indexed (same order as the parameters).
     *                             The array can also contain DI definitions, e.g. DI\get().
     * @return mixed Result of the function.
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     */
    public function call($callable, array $parameters = []) : mixed
    {
        return $this->getInvoker()->call($callable, $parameters);
    }

    /**
     * Define an object or a value in the container.
     *
     * @param string $name Entry name
     * @param mixed|DefinitionHelperInterface $value Value, use definition helpers to define objects
     */
    public function set(string $name, mixed $value) : void
    {
        if ($value instanceof DefinitionHelperInterface) {
            $value = $value->getDefinition($name);
        } elseif ($value instanceof Closure) {
            $value = new FactoryDefinition($name, $value);
        }

        if ($value instanceof ValueDefinition) {
            $this->resolvedEntries[$name] = $value->getValue();
        } elseif ($value instanceof Definition) {
            $value->setName($name);
            $this->setDefinition($name, $value);
        } else {
            $this->resolvedEntries[$name] = $value;
        }
    }

    /**
     * Get defined container entries.
     *
     * @return string[]
     */
    public function getKnownEntryNames() : array
    {
        $entries = array_unique(array_merge(
            array_keys($this->definitionSource->getDefinitions()),
            array_keys($this->resolvedEntries)
        ));
        sort($entries);

        return $entries;
    }

    /**
     * Get entry debug information.
     *
     * @param string $name Entry name
     *
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    public function debugEntry(string $name) : string
    {
        $definition = $this->definitionSource->getDefinition($name);
        if ($definition instanceof Definition) {
            return (string) $definition;
        }

        if (array_key_exists($name, $this->resolvedEntries)) {
            return $this->getEntryType($this->resolvedEntries[$name]);
        }

        throw new NotFoundException(sprintf("No entry or class found for '%s'", $name));
    }

    /**
     * Get formatted entry type.
     */
    private function getEntryType(mixed $entry) : string
    {
        if (is_object($entry)) {
            return sprintf("Object (\n    class = %s\n)", $entry::class);
        }

        if (is_array($entry)) {
            return preg_replace(['/^array \(/', '/\)$/'], ['[', ']'], var_export($entry, true));
        }

        if (is_string($entry)) {
            return sprintf('Value (\'%s\')', $entry);
        }

        if (is_bool($entry)) {
            return sprintf('Value (%s)', $entry === true ? 'true' : 'false');
        }

        return sprintf('Value (%s)', is_scalar($entry) ? (string) $entry : ucfirst(gettype($entry)));
    }

    /**
     * Resolves a definition.
     *
     * Checks for circular dependencies while resolving the definition.
     *
     * @param Definition $definition
     * @param array $parameters
     * @return mixed
     * @throws DependencyException Error while resolving the entry.
     * @throws InvalidDefinitionException
     */
    private function resolveDefinition(Definition $definition, array $parameters = []) : mixed
    {
        $entryName = $definition->getName();

        // Check if we are already getting this entry -> circular dependency
        if (isset($this->entriesBeingResolved[$entryName])) {
            $entryList = implode(' -> ', [...array_keys($this->entriesBeingResolved), $entryName]);
            throw new DependencyException(
                sprintf(
                "Circular dependency detected while trying to resolve entry '%s': Dependencies: '%s'",
                    $entryName,
                    $entryList
                )
            );
        }
        $this->entriesBeingResolved[$entryName] = true;

        // Resolve the definition
        try {
            $value = $this->definitionResolver->resolve($definition, $parameters);
        } finally {
            unset($this->entriesBeingResolved[$entryName]);
        }

        return $value;
    }

    protected function setDefinition(string $name, Definition $definition) : void
    {
        // Clear existing entry if it exists
        if (array_key_exists($name, $this->resolvedEntries)) {
            unset($this->resolvedEntries[$name]);
        }
        $this->fetchedDefinitions = []; // Completely clear this local cache

        $this->definitionSource->addDefinition($definition);
    }

    /**
     * @return InvokerInterface
     */
    private function getInvoker() : InvokerInterface
    {
        if (! $this->invoker) {
            $parameterResolver = new ResolverChain([
                new DefinitionParameterResolver($this->definitionResolver),
                new NumericArrayResolver,
                new AssociativeArrayResolver,
                new DefaultValueResolver,
                new TypeHintContainerResolver($this->delegateContainer),
            ]);

            $this->invoker = new Invoker($parameterResolver, $this);
        }

        return $this->invoker;
    }

    /**
     * @param array $definitions
     * @return SourceChain
     * @throws Exception
     */
    private function createDefaultDefinitionSource(array $definitions) : SourceChain
    {
        $autowiring = new ReflectionBasedAutowiring;
        $source = new SourceChain([$autowiring]);
        $source->setMutableDefinitionSource(new DefinitionArray($definitions, $autowiring));

        return $source;
    }

    /**
     * Set entry alias container.
     *
     * @param string $abstract
     * @param string $alias
     * @return void
     * @throws Exception
     */
    public function alias(string $abstract, string $alias): void
    {
        if ($abstract === $alias) {
            throw new Exception(sprintf("'%s' is aliased to itself.", $abstract));
        }

        $this->aliases[$alias] = $abstract;
    }

    /**
     * Get alias for an abstract if available.
     *
     * @param string $abstract
     * @return string
     */
    public function getAlias(string $abstract): string
    {
        return array_key_exists($abstract, $this->aliases)
            ? $this->getAlias($this->aliases[$abstract])
            : $abstract;
    }

    /**
     * Flush container.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->aliases              = [];
        $this->resolvedEntries      = [];
        $this->entriesBeingResolved = [];
    }

    /**
     * Offest exist check.
     *
     * @param string $offset
     * @return bool
     * @throws InvalidDefinitionException
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Get the value.
     *
     * @param string|class-string<mixed> $offset entry name or a class name
     * @return mixed
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->make($offset);
    }

    /**
     * Set the value.
     *
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Unset the value.
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->resolvedEntries[$offset]);
    }
}
