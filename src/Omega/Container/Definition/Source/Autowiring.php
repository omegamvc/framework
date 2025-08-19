<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Source;

use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Definition\ObjectDefinition;

/**
 * Source of definitions for entries of the container.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface Autowiring
{
    /**
     * Autowire the given definition.
     *
     * @throws InvalidDefinitionException An invalid definition was found.
     */
    public function autowire(string $name, ?ObjectDefinition $definition = null) : ?ObjectDefinition;
}
