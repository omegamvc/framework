<?php

declare(strict_types=1);

namespace Omega\Container\Definition;

/**
 * Factory that decorates a sub-definition.
 */
class DecoratorDefinition extends FactoryDefinition implements DefinitionInterface, ExtendsPreviousDefinitionInterface
{
    /** @var DefinitionInterface|null */
    private ?DefinitionInterface $decorated = null;

    /**
     * @param DefinitionInterface $definition
     * @return void
     */
    public function setExtendedDefinition(DefinitionInterface $definition): void
    {
        $this->decorated = $definition;
    }

    /**
     * @return DefinitionInterface|null
     */
    public function getDecoratedDefinition(): ?DefinitionInterface
    {
        return $this->decorated;
    }

    /**
     * @param callable $replacer
     * @return void
     */
    public function replaceNestedDefinitions(callable $replacer): void
    {
        // no nested definitions
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'Decorate(' . $this->getName() . ')';
    }
}
