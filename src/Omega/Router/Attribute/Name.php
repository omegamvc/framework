<?php

declare(strict_types=1);

namespace Omega\Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Name
{
    public function __construct(
        public string $name,
    ) {
    }
}
