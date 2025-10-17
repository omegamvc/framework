<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Resolver;

use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;

/**
 * Resolves a definition to a value.
 *
 * @template T of DefinitionInterface
 */
interface DefinitionResolverInterface
{
    /**
     * Resolve a definition to a value.
     *
     * @param DefinitionInterface $definition Object that defines how the value should be obtained.
     * @param array      $parameters Optional parameters to use to build the entry.
     * @return mixed Value obtained from the definition.
     * @throws DependencyException
     * @throws InvalidDefinitionException If the definition cannot be resolved.
     */
    public function resolve(DefinitionInterface $definition, array $parameters = []): mixed;

    /**
     * Check if a definition can be resolved.
     *
     * @param DefinitionInterface $definition Object that defines how the value should be obtained.
     * @param array      $parameters Optional parameters to use to build the entry.
     */
    public function isResolvable(DefinitionInterface $definition, array $parameters = []): bool;
}
