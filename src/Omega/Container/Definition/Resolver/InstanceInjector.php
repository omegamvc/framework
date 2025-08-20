<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Resolver;

use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Definition\InstanceDefinition;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundExceptionInterface;
use ReflectionException;

use function get_class;
use function sprintf;

/**
 * Injects dependencies on an existing instance.
 *
 * @template-implements DefinitionResolverInterface<InstanceDefinition>
 */
class InstanceInjector extends ObjectCreator implements DefinitionResolverInterface
{
    /**
     * Injects dependencies on an existing instance.
     *
     * @param DefinitionInterface $definition
     * @param array $parameters
     * @return object|null
     * @throws DependencyException
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws ReflectionException
     */
    public function resolve(DefinitionInterface $definition, array $parameters = []) : ?object
    {
        /** @psalm-suppress InvalidCatch */
        try {
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $this->injectMethodsAndProperties($definition->getInstance(), $definition->objectDefinition);
        } catch (NotFoundExceptionInterface $e) {
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $message = sprintf(
                'Error while injecting dependencies into %s: %s',
                get_class($definition->getInstance()),
                $e->getMessage()
            );

            throw new DependencyException($message, 0, $e);
        }

        return $definition;
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
