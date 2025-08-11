<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use RuntimeException;

interface FacadeInterface
{
    /**
     * Get accessor from application.
     *
     * @return string
     */
    public static function getFacadeAccessor(): string;
}
