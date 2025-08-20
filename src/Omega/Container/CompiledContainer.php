<?php

declare(strict_types=1);

namespace Omega\Container;

use LogicException;
use Omega\Container\Compiler\RequestedEntryHolder;
use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Container\Invoker\Exception\InvocationException;
use Omega\Container\Invoker\Exception\NotCallableException;
use Omega\Container\Invoker\Exception\NotEnoughParametersException;
use Omega\Container\Invoker\FactoryParameterResolver;
use Omega\Container\Invoker\Invoker;
use Omega\Container\Invoker\InvokerInterface;
use Omega\Container\Invoker\ParameterResolver\AssociativeArrayResolver;
use Omega\Container\Invoker\ParameterResolver\DefaultValueResolver;
use Omega\Container\Invoker\ParameterResolver\NumericArrayResolver;
use Omega\Container\Invoker\ParameterResolver\ResolverChain;

use function array_key_exists;
use function array_keys;
use function implode;

/**
 * Compiled version of the dependency injection container.
 */
abstract class CompiledContainer extends Container
{
    /** @var array This const is overridden in child classes (compiled containers). */
    protected const array METHOD_MAPPING = [];

    /** @var InvokerInterface|null  */
    private ?InvokerInterface $factoryInvoker = null;

    /**
     * @param string $id
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidDefinitionException
     */
    public function get(string $id) : mixed
    {
        // Try to find the entry in the singleton map
        if (isset($this->resolvedEntries[$id]) || array_key_exists($id, $this->resolvedEntries)) {
            return $this->resolvedEntries[$id];
        }

        $method = static::METHOD_MAPPING[$id] ?? null;

        // If it's a compiled entry, then there is a method in this class
        if ($method !== null) {
            // Check if we are already getting this entry -> circular dependency
            if (isset($this->entriesBeingResolved[$id])) {
                $idList = implode(' -> ', [...array_keys($this->entriesBeingResolved), $id]);
                throw new DependencyException(
                    sprintf(
                        "Circular dependency detected while trying to resolve entry '%s': Dependencies: '%s'",
                        $id,
                        $idList
                    )
                );
            }
            $this->entriesBeingResolved[$id] = true;

            try {
                $value = $this->$method();
            } finally {
                unset($this->entriesBeingResolved[$id]);
            }

            // Store the entry to always return it without recomputing it
            $this->resolvedEntries[$id] = $value;

            return $value;
        }

        return parent::get($id);
    }

    public function has(string $id) : bool
    {
        // The parent method is overridden to check in our array, it avoids resolving definitions
        if (isset(static::METHOD_MAPPING[$id])) {
            return true;
        }

        return parent::has($id);
    }

    /**
     * @param string $name
     * @param DefinitionInterface $definition
     * @return void
     */
    protected function setDefinition(string $name, DefinitionInterface $definition): void
    {
        throw new LogicException(
            'You cannot set a definition at runtime on a compiled container. ' .
            'This would force get() to process definitions every time, ' .
            'defeating the performance gains of the compiled container. ' .
            'You can either put your definitions in a file, disable compilation, ' .
            'or ->set() a raw value directly (PHP object, string, int, ...) ' .
            'instead of a PHP-DI definition.'
        );
    }

    /**
     * Invoke the given callable.
     *
     * @param $callable
     * @param $entryName
     * @param array $extraParameters
     * @return mixed
     * @throws InvalidDefinitionException
     * @throws InvocationException
     *
     */
    protected function resolveFactory($callable, $entryName, array $extraParameters = []) : mixed
    {
        // Initialize the factory resolver
        if (! $this->factoryInvoker) {
            $parameterResolver = new ResolverChain([
                new AssociativeArrayResolver,
                new FactoryParameterResolver($this->delegateContainer),
                new NumericArrayResolver,
                new DefaultValueResolver,
            ]);

            $this->factoryInvoker = new Invoker($parameterResolver, $this->delegateContainer);
        }

        $parameters = [$this->delegateContainer, new RequestedEntryHolder($entryName)];

        $parameters = array_merge($parameters, $extraParameters);

        try {
            return $this->factoryInvoker->call($callable, $parameters);
        } catch (NotCallableException $e) {
            throw new InvalidDefinitionException(
                "Entry \"$entryName\" cannot be resolved: factory " . $e->getMessage()
            );
        } catch (NotEnoughParametersException $e) {
            throw new InvalidDefinitionException(
                "Entry \"$entryName\" cannot be resolved: " . $e->getMessage()
            );
        }
    }
}
