<?php

declare(strict_types=1);

namespace Omega\Template;

class ConstPool
{
    /** @var Constant[] */
    private array $pools = [];

    public function name(string $name): Constant
    {
        return $this->pools[] = new Constant($name);
    }

    /**
     * @return Constant[]
     */
    public function getPools(): array
    {
        return $this->pools;
    }
}
