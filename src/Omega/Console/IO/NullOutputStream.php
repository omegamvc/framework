<?php

declare(strict_types=1);

namespace Omega\Console\IO;

class NullOutputStream implements OutputStreamInterface
{
    public function write(string $buffer): void
    {
    }

    public function isInteractive(): bool
    {
        return false;
    }
}
