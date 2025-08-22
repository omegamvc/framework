<?php

declare(strict_types=1);

namespace Omega\Container\Definition;

use Omega\Container\Factory\RequestedEntryInterface;

/**
 * Definition of a container entry.
 *
 * Extends RequestedEntryInterface: every Definition must implement getName(): string.
 */
interface DefinitionInterface extends RequestedEntryInterface, \Stringable
{
    /**
     * Set the name of the entry in the container.
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name) : void;

    /**
     * Apply a callable that replaces the definitions nested in this definition.
     *
     * @param callable $replacer
     * @return void
     */
    public function replaceNestedDefinitions(callable $replacer) : void;

    /**
     * Definitions can be cast to string for debugging information.
     *
     * @return string
     */
    public function __toString() : string;
}
