<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Resolver;

use Omega\Container\Definition\Definition;
use Omega\Container\Definition\Exception\InvalidDefinition;
use Omega\Container\DependencyException;

/**
 * Resolves a definition to a value.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 *
 * @template T of Definition
 */
interface DefinitionResolver
{
    /**
     * Resolve a definition to a value.
     *
     * @param Definition $definition Object that defines how the value should be obtained.
     * @psalm-param T $definition
     * @param array      $parameters Optional parameters to use to build the entry.
     * @return mixed Value obtained from the definition.
     *
     * @throws InvalidDefinition If the definition cannot be resolved.
     * @throws DependencyException
     */
    public function resolve(Definition $definition, array $parameters = []) : mixed;

    /**
     * Check if a definition can be resolved.
     *
     * @param Definition $definition Object that defines how the value should be obtained.
     * @psalm-param T $definition
     * @param array      $parameters Optional parameters to use to build the entry.
     */
    public function isResolvable(Definition $definition, array $parameters = []) : bool;
}
