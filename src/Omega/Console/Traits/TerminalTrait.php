<?php

/**
 * Part of Omega - Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

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

/**
 * Trait TerminalTrait
 *
 * Provides functionality to determine the terminal's current width,
 * with cross-platform support and fallbacks for environments
 * where width cannot be detected automatically.
 *
 * @category   Omega
 * @package    Console
 * @subpackage Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
trait TerminalTrait
{
    /**
     * Get the current terminal width, constrained between minimum and maximum values.
     *
     * This method attempts to retrieve the terminal width from environment variables,
     * shell commands (`mode con` on Windows or `stty size` on Unix), or defaults to the
     * minimum width if detection fails or shell_exec is disabled.
     *
     * @param int $min The minimum width to return (default: 80).
     * @param int $max The maximum width to return (default: 160).
     * @return int The detected terminal width, bounded between min and max.
     */
    protected function getWidth(int $min = 80, int $max = 160): int
    {
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
     * Clamp a value between a minimum and maximum range.
     *
     * @param int $value The value to evaluate.
     * @param int $min The minimum allowed value.
     * @param int $max The maximum allowed value.
     * @return int The clamped value.
     */
    private function minMax(int $value, int $min, int $max): int
    {
        /** @noinspection PhpConditionCanBeReplacedWithMinMaxCallInspection */
        return $value < $min ? $min : ($value > $max ? $max : $value);
    }
}
