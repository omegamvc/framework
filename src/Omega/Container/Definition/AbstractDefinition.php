<?php

declare(strict_types=1);

namespace Omega\Container\Definition;

abstract class AbstractDefinition implements DefinitionInterface
{
    protected string $name = '';

    public function __construct(?string $name = null)
    {
        if ($name !== null) {
            $this->name = $name;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name = null): void
    {
        if ($name !== null) {
            $this->name = $name;
        }
    }
}
