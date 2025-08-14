<?php

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
 * @implements ArrayAccess<string, string|string[]|(array<string, string|bool|int|null>)|(callable(string): bool)>
 */
class CommandMap implements ArrayAccess
{
    /** @var array<string, string|string[]|(array<string, string|bool|int|null>)|(callable(string): bool)> */
    private array $command;

    /**
     * @param array<string, string|string[]|(array<string, string|bool|int|null>)|(callable(string): bool)> $command
     */
    public function __construct(array $command)
    {
        $this->command = $command;
    }

    /**
     * Command rule wrap to array.
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
     * @return string
     */
    public function mode(): string
    {
        return $this->command['mode'] ?? 'full';
    }

    /**
     * Pattern rule wrap to array.
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
     * @return string
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
     * @return string|string[]
     */
    public function fn(): string|array
    {
        return $this->command['fn'] ?? 'main';
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return is_array($this->fn()) ? $this->fn()[1] : $this->fn();
    }

    /**
     * @return array<string, string|bool|int|null>
     */
    public function defaultOption(): array
    {
        return $this->command['default'] ?? [];
    }

    /**
     * @return callable(string): bool
     */
    public function match(): callable
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
     * @param string $given
     * @return bool
     */
    public function isMatch(string $given): bool
    {
        return ($this->match())($given);
    }

    /**
     * Call user using class and method.
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
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->command);
    }

    /**
     * @param mixed $offset
     * @return string|string[]|(array<string, string|bool|int|null>)|(callable(string): bool)
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->command[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     * @throws Exception
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception('CommandMap cant be reassignment');
    }

    /**
     * @param mixed $offset
     * @return void
     * @throws Exception
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new Exception('CommandMap cant be reassignment');
    }
}
