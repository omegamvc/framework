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

namespace Omega\Console\Style\Color;

/**
 * Interface RuleInterface
 *
 * Defines a contract for formatting and retrieving tabular alignment rules
 * used in console table rendering.
 *
 * Implementations of this interface provide both the raw string representation
 * of alignment rules (as passed by the user or defined in config), and the
 * parsed structure used internally to apply formatting.
 *
 * @category   Omega
 * @package    Console
 * @subpackage Style\Color
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
interface RuleInterface
{
    /**
     * Returns the parsed alignment rules as an array of column alignments.
     *
     * Each value in the array represents a column and its corresponding alignment:
     * -1 for left-aligned, 0 for center-aligned, 1 for right-aligned.
     *
     * @return array<int, int> The list of alignment codes by column index.
     */
    public function get(): array;

    /**
     * Returns the raw string representation of the alignment rules.
     *
     * This is typically a string like 'lcr' or 'r|c|l' used to define how
     * each column should be aligned in a table format.
     *
     * @return string The original unprocessed rule string.
     */
    public function raw(): string;
}
