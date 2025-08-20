<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Resolver;

use Omega\Container\ContainerInterface;
use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Definition\FactoryDefinition;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Invoker\Exception\InvocationException;
use Omega\Container\Invoker\FactoryParameterResolver;
use Omega\Container\Invoker\Exception\NotCallableException;
use Omega\Container\Invoker\Exception\NotEnoughParametersException;
use Omega\Container\Invoker\Invoker;
use Omega\Container\Invoker\ParameterResolver\AssociativeArrayResolver;
use Omega\Container\Invoker\ParameterResolver\DefaultValueResolver;
use Omega\Container\Invoker\ParameterResolver\NumericArrayResolver;
use Omega\Container\Invoker\ParameterResolver\ResolverChain;

use function class_exists;
use function is_string;
use function method_exists;
use function sprintf;

/**
 * Resolves a factory definition to a value.
 *
 * @template-implements DefinitionResolverInterface<FactoryDefinition>
 */
class FactoryResolver implements DefinitionResolverInterface
{
    /** @var Invoker|null  */
    private ?Invoker $invoker = null;

    /**
     * The resolver needs a container. This container will be passed to the factory as a parameter
     * so that the factory can access other entries of the container.
     */
    public function __construct(
        private readonly ContainerInterface          $container,
        private readonly DefinitionResolverInterface $resolver,
    ) {
    }

    /**
     * Resolve a factory definition to a value.
     *
     * This will call the callable of the definition.
     *
     * @param FactoryDefinition $definition
     * @param array             $parameters
     * @return mixed
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws InvocationException
     */
    public function resolve(DefinitionInterface $definition, array $parameters = []) : mixed
    {
        if (! $this->invoker) {
            $parameterResolver = new ResolverChain([
                new AssociativeArrayResolver,
                new FactoryParameterResolver($this->container),
                new NumericArrayResolver,
                new DefaultValueResolver,
            ]);

            $this->invoker = new Invoker($parameterResolver, $this->container);
        }

        $callable = $definition->getCallable();

        try {
            $providedParams = [$this->container, $definition];
            $extraParams = $this->resolveExtraParams($definition->getParameters());
            $providedParams = array_merge($providedParams, $extraParams, $parameters);

            return $this->invoker->call($callable, $providedParams);
        } catch (NotCallableException $e) {
            // Custom error message to help debugging
            if (is_string($callable) && class_exists($callable) && method_exists($callable, '__invoke')) {
                throw new InvalidDefinitionException(
                    sprintf(
                        'Entry "%s" cannot be resolved: factory %s. Invokable classes cannot be automatically'  .
                        'resolved if autowiring is disabled on the container, you need to enable autowiring or define' .
                        'the entry manually.',
                    $definition->getName(),
                    $e->getMessage()
                ));
            }

            throw new InvalidDefinitionException(sprintf(
                'Entry "%s" cannot be resolved: factory %s',
                $definition->getName(),
                $e->getMessage()
            ));
        } catch (NotEnoughParametersException $e) {
            throw new InvalidDefinitionException(sprintf(
                'Entry "%s" cannot be resolved: %s',
                $definition->getName(),
                $e->getMessage()
            ));
        }
    }

    /**
     * @param DefinitionInterface $definition
     * @param array $parameters
     * @return bool
     */
    public function isResolvable(DefinitionInterface $definition, array $parameters = []) : bool
    {
        return true;
    }

    /**
     * @param array $params
     * @return array
     * @throws InvalidDefinitionException
     * @throws DependencyException
     */
    private function resolveExtraParams(array $params) : array
    {
        $resolved = [];
        foreach ($params as $key => $value) {
            // Nested definitions
            if ($value instanceof DefinitionInterface) {
                $value = $this->resolver->resolve($value);
            }
            $resolved[$key] = $value;
        }

        return $resolved;
    }
}
