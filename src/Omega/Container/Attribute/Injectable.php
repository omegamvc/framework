<?php

declare(strict_types=1);

namespace Omega\Container\Attribute;

use Attribute;

/**
 * "Injectable" attribute.
 *
 * Marks a class as injectable
 */
#[Attribute(Attribute::TARGET_CLASS)]
readonly class Injectable
{
    /**
     * @param bool|null $lazy Should the object be lazy-loaded.
     */
    public function __construct(
        private ?bool $lazy = null,
    ) {
    }

    public function isLazy(): ?bool
    {
        return $this->lazy;
    }
}
