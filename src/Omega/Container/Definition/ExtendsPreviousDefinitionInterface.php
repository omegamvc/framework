<?php

declare(strict_types=1);

namespace Omega\Container\Definition;

/**
 * A definition that extends a previous definition with the same name.
 */
interface ExtendsPreviousDefinitionInterface extends DefinitionInterface
{
    /**
     * @param DefinitionInterface $definition
     * @return void
     */
    public function setExtendedDefinition(DefinitionInterface $definition): void;
}
