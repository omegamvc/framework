<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Source;

use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Definition\ObjectDefinition;

/**
 * Source of definitions for entries of the container.
 */
interface AutowiringInterface
{
    /**
     * Autowire the given definition.
     *
     * @param string $name
     * @param ObjectDefinition|null $definition
     * @return ObjectDefinition|null
     * @throws InvalidDefinitionException An invalid definition was found.
     */
    public function autowire(string $name, ?ObjectDefinition $definition = null) : ?ObjectDefinition;
}
