<?php

declare(strict_types=1);

namespace Omega\Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Where
{
    public function __construct(
        /**
         * @var array<string, string>
         */
        public array $pattern,
    ) {
    }
}
