<?php

declare(strict_types=1);

namespace Omega\Template;

class MethodPool
{
    /** @var Method[] */
    private array $pools = [];

    public function name(string $name): Method
    {
        return $this->pools[] = new Method($name);
    }

    /**
     * @return Method[]
     */
    public function getPools(): array
    {
        return $this->pools;
    }
}
