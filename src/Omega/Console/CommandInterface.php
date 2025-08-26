<?php

declare(strict_types=1);

namespace Omega\Console;

interface CommandInterface
{
    /**
     * Default class to run some code.
     *
     * @return int
     */
    public function main(): int;
}
