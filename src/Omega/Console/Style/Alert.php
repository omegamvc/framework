<?php

declare(strict_types=1);

namespace Omega\Console\Style;

use Omega\Console\Traits\AlertTrait;

class Alert
{
    use AlertTrait;

    /**
     * New instance.
     *
     * @return static Return static instance for method chaining.
     */
    public static function render(): static
    {
        return new self();
    }
}
