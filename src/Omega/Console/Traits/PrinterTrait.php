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

use Omega\Console\Style\Decorate;

use function chr;
use function implode;
use function str_repeat;

/**
 * Trait PrinterTrait
 *
 * Provides low-level utilities for styling and manipulating terminal output using ANSI escape codes.
 * Includes methods for applying formatting rules, line and tab spacing, and manipulating cursor position.
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
trait PrinterTrait
{
    /**
     * Apply multiple formatting rules to the given text.
     *
     * @param array<int, string|int> $rule ANSI code(s) to apply (e.g., color, style).
     * @param string|int $text The text to style.
     * @param bool $reset Whether to reset formatting after the text.
     * @param array<int, string|int> $resetRule ANSI code(s) to reset styles (default: reset all).
     * @return string The styled string with escape sequences applied.
     */
    protected function rules(
        array $rule,
        string|int $text,
        bool $reset = true,
        array $resetRule = [Decorate::RESET]
    ): string {
        $stringRules      = implode(';', $rule);
        $stringResetRules = implode(';', $resetRule);

        return $this->rule($stringRules, $text, $reset, $stringResetRules);
    }

    /**
     * Apply a single formatting rule to the given text.
     *
     * @param int|string $rule The ANSI rule to apply (e.g., "1" for bold).
     * @param string $text The text to style.
     * @param bool $reset Whether to reset formatting after the text.
     * @param int|string $resetRule The reset code to append after the text (default: reset all).
     * @return string The styled string with escape sequences applied.
     */
    protected function rule(
        int|string $rule,
        string $text,
        bool $reset = true,
        int|string $resetRule = Decorate::RESET
    ): string {
        $rule      = chr(27) . '[' . $rule . 'm' . $text;
        $resetRule = chr(27) . '[' . $resetRule . 'm';

        return $reset
            ? $rule . $resetRule
            : $rule;
    }

    /**
     * Output one or more newlines to the console.
     *
     * @deprecated Use newLine() instead to return the string rather than echoing it.
     *
     * @param int $count Number of newlines to print.
     * @return void
     */
    protected function print_n(int $count = 1): void
    {
        echo str_repeat("\n", $count);
    }

    /**
     * Output one or more tab characters to the console.
     *
     * @deprecated Use tabs() instead to return the string rather than echoing it.
     *
     * @param int $count Number of tabs to print.
     * @return void
     */
    protected function print_t(int $count = 1): void
    {
        echo str_repeat("\t", $count);
    }

    /**
     * Return a string containing one or more newline characters.
     *
     * @param int $count Number of newline characters.
     * @return string The generated newline string.
     */
    protected function newLine(int $count = 1): string
    {
        return str_repeat("\n", $count);
    }

    /**
     * Return a string containing one or more tab characters.
     *
     * @param int $count Number of tab characters.
     * @return string The generated tab string.
     */
    protected function tabs(int $count = 1): string
    {
        return str_repeat("\t", $count);
    }

    /**
     * Clear the terminal from the cursor position to the beginning of the line.
     *
     * @deprecated Not recommended for modern CLI output handling.
     *
     * @return void
     */
    protected function clearCursor(): void
    {
        echo chr(27) . '[1K';
    }

    /**
     * Clear the entire current line in the terminal.
     *
     * @deprecated Use clearLine() instead for more precise control.
     *
     * @return void
     */
    protected function clear_line(): void
    {
        echo chr(27) . '[2K';
    }

    /**
     * Replace the content of a specific terminal line with new text.
     *
     * @param string $replace The replacement text to display.
     * @param int $line The line offset from the current cursor position (negative = up).
     * @return void
     */
    protected function replaceLine(string $replace, int $line = -1): void
    {
        $this->moveLine($line);
        echo chr(27) . "[K\r" . $replace;
    }

    /**
     * Clear the content of a specific terminal line.
     *
     * @param int $line The line offset from the current cursor position (negative = up).
     * @return void
     */
    protected function clearLine(int $line = -1): void
    {
        $this->moveLine($line);
        $this->replaceLine('');
    }

    /**
     * Move the cursor up by a specified number of lines.
     *
     * @param int $line Number of lines to move up.
     * @return void
     */
    protected function moveLine(int $line): void
    {
        echo chr(27) . "[{$line}A";
    }
}
