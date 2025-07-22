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

namespace Omega\Console;

use Exception;
use Omega\Console\Style\Alert;
use Omega\Console\Style\Style;
use Omega\Console\Traits\TerminalTrait;

use function constant;
use function defined;
use function function_exists;
use function pcntl_signal;
use function posix_getpid;
use function posix_kill;
use function sapi_windows_set_ctrl_handler;

use const PHP_SAPI;
use const PHP_WINDOWS_EVENT_CTRL_C;

/**
 * Terminal and Prompt Helper Functions.
 *
 * This file provides a set of global helper functions to simplify
 * the use of styled console output and interactive terminal prompts.
 *
 * It includes utilities to:
 * - Apply terminal styles to text output (e.g., info, warn, fail, ok).
 * - Display and handle interactive command line prompts:
 *   - Options and selections
 *   - Text and password inputs
 *   - Key press detection
 * - Manage terminal dimensions.
 * - Register and remove CTRL+C (SIGINT) handlers for graceful exits.
 *
 * These helpers are intended to enhance CLI user interaction and streamline
 * terminal-based application development.
 *
 * Dependencies:
 * - Style: for styled terminal output.
 * - Alert: wrapper for common styled messages.
 * - Prompt: for building and handling interactive prompts.
 * - TerminalTrait: for terminal-related utilities (e.g., width detection).
 *
 * Note: All functions are conditionally defined to avoid redeclaration.
 *
 * @category  Omega
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

if (!function_exists('style')) {
    /**
     * Create a styled terminal output instance.
     *
     * This helper initializes a Style object that allows method chaining
     * for rendering styled console output.
     *
     * @param string $text The text to style.
     * @return Style A new Style instance.
     */
    function style(string $text): Style
    {
        return new Style($text);
    }
}

if (!function_exists('info')) {
    /**
     * Display an informational alert message in the terminal.
     *
     * @param string $text The info message.
     * @return Style A styled output instance.
     */
    function info(string $text): Style
    {
        return Alert::render()->info($text);
    }
}

if (!function_exists('warn')) {
    /**
     * Display a warning alert message in the terminal.
     *
     * @param string $text The warning message.
     * @return Style A styled output instance.
     */
    function warn(string $text): Style
    {
        return Alert::render()->warn($text);
    }
}

if (!function_exists('fail')) {
    /**
     * Display an error/failure alert message in the terminal.
     *
     * @param string $text The error message.
     * @return Style A styled output instance.
     */
    function fail(string $text): Style
    {
        return Alert::render()->fail($text);
    }
}

if (!function_exists('ok')) {
    /**
     * Display a success alert message in the terminal.
     *
     * @param string $text The success message.
     * @return Style A styled output instance.
     */
    function ok(string $text): Style
    {
        return Alert::render()->ok($text);
    }
}

if (!function_exists('option')) {
    /**
     * Display a command-line option prompt.
     *
     * Prompts the user to choose between multiple options by key.
     *
     * @param Style|string $title The prompt title.
     * @param array<string, callable> $options The available options.
     * @return mixed The result of the selected option's callback.
     * @throws Exception
     */
    function option(Style|string $title, array $options): mixed
    {
        return (new Prompt($title, $options))->option();
    }
}

if (!function_exists('select')) {
    /**
     * Display a selectable list prompt in the terminal.
     *
     * Prompts the user to select one option from a visual list.
     *
     * @param Style|string $title The prompt title.
     * @param array<string, callable> $options The selectable options.
     * @return mixed The result of the selected option's callback.
     * @throws Exception
     */
    function select(Style|string $title, array $options): mixed
    {
        return (new Prompt($title, $options))->select();
    }
}

if (!function_exists('text')) {
    /**
     * Prompt the user to input free text in the terminal.
     *
     * @param Style|string $title The prompt title.
     * @param callable $callable A callback to validate or process the input.
     * @return mixed The processed input result.
     * @throws Exception
     */
    function text(Style|string $title, callable $callable): mixed
    {
        return (new Prompt($title))->text($callable);
    }
}

if (!function_exists('password')) {
    /**
     * Prompt the user to enter a password (hidden input).
     *
     * @param Style|string $title    The prompt title.
     * @param callable     $callable A callback to validate or process the password.
     * @param string       $mask     The character used to mask the input.
     * @return mixed The processed password result.
     */
    function password(Style|string $title, callable $callable, string $mask = ''): mixed
    {
        return (new Prompt($title))->password($callable, $mask);
    }
}

if (!function_exists('any_key')) {
    /**
     * Wait for any key press and trigger a callback.
     *
     * @param Style|string $title    The prompt title.
     * @param callable     $callable A callback to execute after key press.
     * @return mixed The result of the callback.
     */
    function any_key(Style|string $title, callable $callable): mixed
    {
        return (new Prompt($title))->anyKey($callable);
    }
}

if (!function_exists('width')) {
    /**
     * Get the current terminal width, constrained by min and max bounds.
     *
     * @param int $min The minimum width.
     * @param int $max The maximum width.
     * @return int The calculated terminal width.
     */
    function width(int $min, int $max): int
    {
        $terminal = new class {
            use TerminalTrait;

            public function width(int $min, int $max): int
            {
                return $this->getWidth($min, $max);
            }
        };

        return $terminal->width($min, $max);
    }
}

if (!function_exists('exit_prompt')) {
    /**
     * Register a handler for Ctrl+C (SIGINT) to show an exit confirmation prompt.
     *
     * On interruption, prompts the user whether to exit or continue.
     * Works on both Unix and Windows CLI environments.
     *
     * @param Style|string $title The prompt title.
     * @param array<string, callable>|null $options Custom exit options, defaults to yes/no.
     * @return void
     * @throws Exception
     */
    function exit_prompt(Style|string $title, ?array $options = null): void
    {
        $signal = defined('SIGINT') ? constant('SIGINT') : 2;
        $options ??= [
            'yes' => static function () use ($signal) {
                if (function_exists('posix_kill') && function_exists('posix_getpid')) {
                    posix_kill(posix_getpid(), $signal);
                }

                exit(128 + $signal);
            },
            'no' => fn () => null,
        ];

        if (function_exists('sapi_windows_set_ctrl_handler') && 'cli' === PHP_SAPI) {
            sapi_windows_set_ctrl_handler(static function (int $event) use ($title, $options) {
                if (PHP_WINDOWS_EVENT_CTRL_C === $event) {
                    (new Style())->out();
                    (new Prompt($title, $options, 'no'))->option();
                }
            });
        }

        if (function_exists('pcntl_signal')) {
            pcntl_signal($signal, $options['yes']);
        }
    }
}

if (!function_exists('remove_exit_prompt')) {
    /**
     * Remove the registered Ctrl+C (SIGINT) signal handler.
     *
     * Resets the signal handling to its default behavior.
     *
     * @return void
     */
    function remove_exit_prompt(): void
    {
        if (function_exists('sapi_windows_set_ctrl_handler') && 'cli' === PHP_SAPI) {
            sapi_windows_set_ctrl_handler(function (int $handler): void {
            }, false);
        }

        $signal  = defined('SIGINT') ? constant('SIGINT') : 2;
        $default = defined('SIG_DFL') ? constant('SIG_DFL') : 0;
        if (function_exists('pcntl_signal')) {
            pcntl_signal($signal, $default);
        }
    }
}
