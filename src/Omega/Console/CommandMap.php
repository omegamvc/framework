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

use ArrayAccess;
use Exception;
use InvalidArgumentException;
use Omega\Text\Str;
use ReturnTypeWillChange;

use function array_key_exists;
use function is_array;

/**
 * Class CommandMap
 *
 * Represents a command definition map that can be accessed like an array
 * and provides helpers to extract execution parameters, class/method references,
 * matching patterns, and options.
 *
 * Commonly used to manage CLI command routing and invocation logic.
 *
 * Implements ArrayAccess for convenient array-like access to internal command config.
 *
 * @category  Omega
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @implements ArrayAccess<string, string|string[]|(array<string, string|bool|int|null>)|(callable(string): bool)>
 */
class CommandMap implements ArrayAccess
{
    /**
     * The internal command definition array.
     *
     * Can contain keys like:
     * - 'cmd'     => string|string[]
     * - 'mode'    => string
     * - 'pattern' => string|string[]
     * - 'fn'      => string|string[]
     * - 'class'   => string
     * - 'default' => array|string
     * - 'match'   => callable|string|array
     *
     * @var array<string, string|string[]|(array<string, string|bool|int|null>)|(callable(string): bool)>
     */
    private array $command;

    /**
     * Constructor.
     *
     * @param array<string, string|string[]|(array<string, string|bool|int|null>)|(callable(string): bool)> $command
     * @return void
     */
    public function __construct(array $command)
    {
        $this->command = $command;
    }

    /**
     * Returns the list of command names (aliases).
     *
     * @return string[]
     */
    public function cmd(): array
    {
        if (false === array_key_exists('cmd', $this->command)) {
            return [];
        }
        $cmd = $this->command['cmd'];

        return is_array($cmd) ? $cmd : [$cmd];
    }

    /**
     * Returns the matching mode ('full' by default).
     *
     * @return string
     */
    public function mode(): string
    {
        return $this->command['mode'] ?? 'full';
    }

    /**
     * Returns the pattern(s) for matching this command.
     *
     * @return string[]
     */
    public function patterns(): array
    {
        if (false === array_key_exists('pattern', $this->command)) {
            return [];
        }
        $pattern = $this->command['pattern'];

        return is_array($pattern) ? $pattern : [$pattern];
    }

    /**
     * Returns the target class for the command.
     *
     * @return string
     * @throws InvalidArgumentException If no class is defined or inferable.
     */
    public function class(): string
    {
        if (is_array($this->fn()) && array_key_exists(0, $this->fn())) {
            return $this->fn()[0];
        }

        if (array_key_exists('class', $this->command)) {
            return $this->command['class'];
        }

        throw new InvalidArgumentException('Command map require class in (class or fn).');
    }

    /**
     * Returns the function to call, either as method name or [class, method] pair.
     *
     * @return string|string[]
     */
    public function fn(): array|string
    {
        return $this->command['fn'] ?? 'main';
    }

    /**
     * Returns the method name to call.
     *
     * @return string
     */
    public function method(): string
    {
        return is_array($this->fn()) ? $this->fn()[1] : $this->fn();
    }

    /**
     * Returns the default option(s) for the command.
     *
     * @return array|string
     */
    public function defaultOption(): array|string
    {
        return $this->command['default'] ?? [];
    }

    /**
     * Returns a callable or matcher for checking if a given command matches this definition.
     *
     * Priority:
     * - pattern
     * - match
     * - cmd + mode fallback
     *
     * @return array|callable|string
     */
    public function match(): array|callable|string
    {
        if (array_key_exists('pattern', $this->command)) {
            $pattern  = $this->command['pattern'];
            $patterns = is_array($pattern) ? $pattern : [$pattern];

            return function ($given) use ($patterns): bool {
                foreach ($patterns as $cmd) {
                    if ($given == $cmd) {
                        return true;
                    }
                }

                return false;
            };
        }

        if (array_key_exists('match', $this->command)) {
            return $this->command['match'];
        }

        if (array_key_exists('cmd', $this->command)) {
            return function ($given): bool {
                foreach ($this->cmd() as $cmd) {
                    if ('full' === $this->mode()) {
                        if ($given == $cmd) {
                            return true;
                        }
                    }

                    if (Str::startsWith($given, $cmd)) {
                        return true;
                    }
                }

                return false;
            };
        }

        return fn ($given) => false;
    }

    /**
     * Checks whether a given input matches this command.
     *
     * @param string $given
     * @return bool
     */
    public function isMatch(string $given): bool
    {
        return ($this->match())($given);
    }

    /**
     * Returns a callable [class, method] pair for execution.
     *
     * @return string[]
     */
    public function call(): array
    {
        return is_array($this->fn())
            ? $this->fn()
            : [$this->class(), $this->fn()];
    }

    /**
     * Checks if the specified offset exists in the command map.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->command);
    }

    /**
     * Returns a value from the command map at the specified offset.
     *
     * @param mixed $offset
     * @return string|string[]|(array<string, string|bool|int|null>)|(callable(string): bool)
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->command[$offset];
    }

    /**
     * Disallows setting a value in the map (immutable).
     *
     * @param mixed $offset
     * @param mixed $value
     * @throws Exception Always thrown to indicate immutability.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception('CommandMap cant be reassignment');
    }

    /**
     * Disallows unsetting a value in the map (immutable).
     *
     * @param mixed $offset
     * @throws Exception Always thrown to indicate immutability.
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new Exception('CommandMap cant be reassignment');
    }
}
