<?php

declare(strict_types=1);

namespace Omega\Container\Compiler;

use Omega\Container\Factory\RequestedEntryInterface;

/**
 */
readonly class RequestedEntryHolder implements RequestedEntryInterface
{
    public function __construct(
        private string $name,
    ) {
    }

    public function getName() : string
    {
        return $this->name;
    }
}
