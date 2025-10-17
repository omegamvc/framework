<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Source;

use Omega\Container\Definition\DefinitionInterface;

/**
 * Describes a definition source to which we can add new definitions.
 */
interface MutableDefinitionSourceInterface extends DefinitionSourceInterface
{
    /**
     * @param DefinitionInterface $definition
     * @return void
     */
    public function addDefinition(DefinitionInterface $definition): void;
}
