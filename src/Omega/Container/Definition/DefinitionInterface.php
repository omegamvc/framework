<?php

declare(strict_types=1);

namespace Omega\Container\Definition;

/**
 * Definition of a container entry.
 *
 * Extends RequestedEntryInterface: every Definition must implement getName(): string.
 */
interface DefinitionInterface extends \Stringable
{
    /**
     * Returns the name of the entry that was requested by the container.
     *
     * @return string
     */
    public function getName() : string;

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
