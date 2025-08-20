<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Resolver;

use Omega\Container\ContainerInterface;
use Omega\Container\Definition\DecoratorDefinition;
use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use function is_callable;

/**
 * Resolves a decorator definition to a value.
 *
 * @template-implements DefinitionResolverInterface<DecoratorDefinition>
 */
class DecoratorResolver implements DefinitionResolverInterface
{
    /**
     * The resolver needs a container. This container will be passed to the factory as a parameter
     * so that the factory can access other entries of the container.
     *
     * @param ContainerInterface $container
     * @param DefinitionResolverInterface $definitionResolver Used to resolve nested definitions.
     */
    public function __construct(
        private ContainerInterface                   $container,
        private readonly DefinitionResolverInterface $definitionResolver,
    ) {
    }

    /**
     * Resolve a decorator definition to a value.
     *
     * This will call the callable of the definition and pass it the decorated entry.
     *
     * @param DecoratorDefinition $definition
     * @param array $parameters
     * @return mixed
     * @throws DependencyException
     * @throws InvalidDefinitionException
     */
    public function resolve(DefinitionInterface $definition, array $parameters = []) : mixed
    {
        $callable = $definition->getCallable();

        if (!is_callable($callable)) {
            throw new InvalidDefinitionException(sprintf(
                'The decorator "%s" is not callable',
                $definition->getName()
            ));
        }

        $decoratedDefinition = $definition->getDecoratedDefinition();

        if (! $decoratedDefinition instanceof DefinitionInterface) {
            if (! $definition->getName()) {
                throw new InvalidDefinitionException('Decorators cannot be nested in another definition');
            }

            throw new InvalidDefinitionException(sprintf(
                'Entry "%s" decorates nothing: no previous definition with the same name was found',
                $definition->getName()
            ));
        }

        $decorated = $this->definitionResolver->resolve($decoratedDefinition, $parameters);

        return $callable($decorated, $this->container);
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
}
