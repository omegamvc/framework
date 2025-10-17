<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Resolver;

use Omega\Container\Definition\ArrayDefinition;
use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Exceptions\DependencyException;
use Exception;

use function array_walk_recursive;

/**
 * Resolves an array definition to a value.
 *
 * @template-implements DefinitionResolverInterface<ArrayDefinition>
 */
readonly class ArrayResolver implements DefinitionResolverInterface
{
    /**
     * @param DefinitionResolverInterface $definitionResolver Used to resolve nested definitions.
     */
    public function __construct(
        private DefinitionResolverInterface $definitionResolver,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * Resolve an array definition to a value.
     *
     * An array definition can contain simple values or references to other entries.
     *
     * @param ArrayDefinition $definition
     * @throws DependencyException
     */
    public function resolve(DefinitionInterface $definition, array $parameters = []): array
    {
        $values = $definition->getValues();

        // Resolve nested definitions
        array_walk_recursive($values, function (&$value, $key) use ($definition) {
            if ($value instanceof DefinitionInterface) {
                $value = $this->resolveDefinition($value, $definition, $key);
            }
        });

        return $values;
    }

    /**
     * @param DefinitionInterface $definition
     * @param array $parameters
     * @return bool
     */
    public function isResolvable(DefinitionInterface $definition, array $parameters = []): bool
    {
        return true;
    }

    /**
     * @param DefinitionInterface      $value
     * @param ArrayDefinition $definition
     * @param int|string      $key
     * @return mixed
     * @throws DependencyException
     */
    private function resolveDefinition(DefinitionInterface $value, ArrayDefinition $definition, int|string $key): mixed
    {
        try {
            return $this->definitionResolver->resolve($value);
        } catch (DependencyException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new DependencyException(sprintf(
                'Error while resolving %s[%s]. %s',
                $definition->getName(),
                $key,
                $e->getMessage()
            ), 0, $e);
        }
    }
}
