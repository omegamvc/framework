<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Source;

use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;

/**
 * Source of definitions for entries of the container.
 */
interface DefinitionSourceInterface
{
    /**
     * Returns the DI definition for the entry name.
     *
     * @param string $name
     * @return DefinitionInterface|null
     * @throws InvalidDefinitionException An invalid definition was found.
     */
    public function getDefinition(string $name) : ?DefinitionInterface;

    /**
     * @return array<string,DefinitionInterface> Definitions indexed by their name.
     */
    public function getDefinitions() : array;
}
