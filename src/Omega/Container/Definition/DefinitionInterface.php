<?php

declare(strict_types=1);

namespace Omega\Container\Definition;

use Omega\Container\Factory\RequestedEntryInterface;

/**
 * Definition.
 *
 * @internal This interface is internal to PHP-DI and may change between minor versions.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface DefinitionInterface extends RequestedEntryInterface, \Stringable
{
    /**
     * Returns the name of the entry in the container.
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
