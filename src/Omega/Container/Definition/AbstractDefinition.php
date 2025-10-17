<?php

declare(strict_types=1);

namespace Omega\Container\Definition;

abstract class AbstractDefinition implements DefinitionInterface
{
    private string $definitionName = '';

    public string $name { // phpcs:ignore
        get {
            return $this->definitionName; // phpcs:ignore
        }
        set {
            $this->definitionName = $value; // phpcs:ignore
        }
    }

    public function __construct(?string $name = null)
    {
        if ($name !==  null) {
            $this->setName($name);
        }
    }

    public function getName(): string
    {
        return $this->definitionName;
    }

    public function setName(?string $name = null): void
    {
        if ($name !== null) {
            $this->definitionName = $name;
        }
    }
}
