<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Resolver;

use Omega\Container\Definition\Definition;
use Omega\Container\Definition\InstanceDefinition;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundExceptionInterface;
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
     * @param Definition $definition
     * @param array $parameters
     * @return object|null
     * @throws DependencyException
     */
    public function resolve(Definition $definition, array $parameters = []) : ?object
    {
        /** @psalm-suppress InvalidCatch */
        try {
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $this->injectMethodsAndProperties($definition->getInstance(), $definition->getObjectDefinition());
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
     * @param Definition $definition
     * @param array $parameters
     * @return bool
     */
    public function isResolvable(Definition $definition, array $parameters = []) : bool
    {
        return true;
    }
}
