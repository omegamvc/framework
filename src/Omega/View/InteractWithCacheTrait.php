<?php

declare(strict_types=1);

namespace Omega\View;

use function array_key_exists;
use function file_get_contents;

trait InteractWithCacheTrait
{
    /**
     * Get contents using cache first.
     */
    private function getContents(string $fileName): string
    {
        if (false === array_key_exists($fileName, self::$cache)) {
            self::$cache[$fileName] = file_get_contents($fileName);
        }

        return self::$cache[$fileName];
    }
}
