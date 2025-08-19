<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Source;

use Omega\Container\Definition\Definition;
use Omega\Container\Definition\Exception\InvalidDefinition;

/**
 * Source of definitions for entries of the container.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface DefinitionSource
{
    /**
     * Returns the DI definition for the entry name.
     *
     * @throws InvalidDefinition An invalid definition was found.
     */
    public function getDefinition(string $name) : ?Definition;

    /**
     * @return array<string,Definition> Definitions indexed by their name.
     */
    public function getDefinitions() : array;
}
