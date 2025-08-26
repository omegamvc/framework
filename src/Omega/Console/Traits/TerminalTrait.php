<?php

declare(strict_types=1);

namespace Omega\Console\Traits;

use function array_key_exists;
use function count;
use function explode;
use function function_exists;
use function preg_match;
use function shell_exec;
use function trim;

use const PHP_OS_FAMILY;

trait TerminalTrait
{
    /**
     * Get terminal width.
     */
    protected function getWidth(int $min = 80, int $max = 160): int
    {
        $custom = env('TERMINAL_COLUMNS');
        if ($custom !== false) {
            return $this->minMax((int) trim((string) $custom), $min, $max);
        }

        if (array_key_exists('COLUMNS', $_ENV)) {
            return $this->minMax((int) trim((string) $_ENV['COLUMNS']), $min, $max);
        }

        if (!function_exists('shell_exec')) {
            return $min;
        }

        if ('Windows' === PHP_OS_FAMILY) {
            $modeOutput = shell_exec('mode con');
            if (preg_match('/Columns:\s+(\d+)/', $modeOutput, $matches)) {
                return $this->minMax((int) $matches[1], $min, $max);
            }

            return $min;
        }

        $sttyOutput = shell_exec('stty size 2>&1');
        if ($sttyOutput) {
            $dimensions = explode(' ', trim($sttyOutput));
            if (2 === count($dimensions)) {
                return $this->minMax((int) $dimensions[1], $min, $max);
            }
        }

        return $min;
    }

    /**
     * Helper to get between min-max value.
     */
    private function minMax(int $value, int $min, int $max): int
    {
        return $value < $min ? $min : (min($value, $max));
    }
}
