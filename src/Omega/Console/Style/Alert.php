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
     * @return self
     */
    public static function render()
    {
        return new self();
    }
}
