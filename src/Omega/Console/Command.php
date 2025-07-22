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
use Omega\Console\Traits\TerminalTrait;
use Omega\Text\Str;
use ReturnTypeWillChange;

use function array_key_exists;
use function array_merge;
use function array_shift;
use function count;
use function explode;
use function is_array;
use function is_int;
use function preg_match;
use function preg_replace;
use function str_split;

/**
 * Command parser and option handler for command-line input.
 *
 * This class parses CLI arguments into a structured array of commands and options,
 * providing convenient access through both property and array syntax.
 *
 * Implements read-only ArrayAccess to access parsed options.
 *
 * Add customize terminal style by adding traits:
 * - TraitCommand (optional).
 *
 * @category  Omega
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @property bool|int|string|string[]|null $_
 * @property bool|int|string|string[]|null $name
 * @property bool|int|string|string[]|null $nick
 * @property bool|int|string|string[]|null $whois
 * @property bool|int|string|string[]|null $default
 * @property bool|int|string|string[]|null $t
 * @property bool|int|string|string[]|null $n
 * @property bool|int|string|string[]|null $s
 * @property bool|int|string|string[]|null $l
 * @property bool|int|string|string[]|null $cp
 * @property bool|int|string|string[]|null $io
 * @property bool|int|string|string[]|null $i
 * @property bool|int|string|string[]|null $o
 * @property bool|int|string|string[]|null $ab
 * @property bool|int|string|string[]|null $a
 * @property bool|int|string|string[]|null $b
 * @property bool|int|string|string[]|null $y
 * @property bool|int|string|string[]|null $d
 * @property bool|int|string|string[]|null $vvv
 * @property bool|int|string|string[]|null $v
 * @property bool|int|string|string[]|null $last
 *
 * @method echoTextRed()
 * @method echoTextYellow()
 * @method echoTextGreen()
 *
 * @implements ArrayAccess<string, string|bool|int|null>
 */
class Command implements ArrayAccess
{
    use TerminalTrait;

    /** @var string|array<int, string> Create new scratch file from selection. */
    protected string|array $cmd;

    /** array<int, string> Raw option strings from the CLI (e.g., --flag, -o=value). */
    protected array $option;

    /** @var string Base working directory. */
    protected string $baseDir;

    /** @var array<string, string|string[]|bool|int|null> Parsed options mapped as key-value pairs. */
    protected array $optionMapper;

    /** @var array<string, string> Command descriptions for help output. */
    protected array $commandDescribes = [];

    /** @var array<string, string> Option descriptions for help output. */
    protected array $optionDescribes = [];

    /** @var array<string, array<int, string>> Relationship between command names and their arguments. */
    protected array $commandRelation = [];

    /**
     * Create a new Command instance and parse CLI arguments.
     *
     * @param array<int, string>                  $argv         Command-line arguments
     * @param array<string, string|bool|int|null> $defaultOption Default option values
     * @return void
     */
    public function __construct(array $argv, array $defaultOption = [])
    {
        array_shift($argv);

        $this->cmd          = array_shift($argv) ?? '';
        $this->option       = $argv;
        $this->optionMapper = $defaultOption;

        foreach ($this->optionMapper($argv) as $key => $value) {
            $this->optionMapper[$key] = $value;
        }
    }

    /**
     * Parse CLI options into a structured associative array.
     *
     * Supports --key=value, --key value, and short aliases like -abc.
     *
     * @param array<int, string|bool|int|null> $argv CLI arguments
     * @return array<string, string|bool|int|null> Parsed options
     */
    private function optionMapper(array $argv): array
    {
        $options      = [];
        $options['_'] = $options['name'] = $argv[0] ?? '';
        $lastOption   = null;
        $alias        = [];

        foreach ($argv as $key => $option) {
            if ($this->isCommandParam($option)) {
                $keyValue = explode('=', $option);
                $name      = preg_replace('/^(-{1,2})/', '', $keyValue[0]);

                // alias check
                /** @noinspection PhpUnusedLocalVariableInspection */
                if (preg_match('/^-(?!-)([a-zA-Z]+)$/', $keyValue[0], $singleDash)) {
                    $alias[$name] = array_key_exists($name, $alias)
                        ? array_merge($alias[$name], str_split($name))
                        : str_split($name);
                }

                // param have value
                if (isset($keyValue[1])) {
                    $options[$name] = $this->removeQuote($keyValue[1]);
                    continue;
                }

                // check value in next param
                $nextKey = $key + 1;

                if (!isset($argv[$nextKey])) {
                    $options[$name] = true;
                    continue;
                }

                $next = $argv[$nextKey];
                if ($this->isCommandParam($next)) {
                    $options[$name] = true;
                }

                $lastOption = $name;
                continue;
            }

            $options[$lastOption][] = $this->removeQuote($option);
        }

        // re-group alias
        foreach ($alias as $key => $names) {
            foreach ($names as $name) {
                if (array_key_exists($name, $options)) {
                    if (is_int($options[$name])) {
                        $options[$name]++;
                    }
                    continue;
                }
                $options[$name] = $options[$key];
            }
        }

        return $options;
    }

    /**
     * Determine if the string is a command-line flag (starts with - or --).
     *
     * @param string $command CLI string
     * @return bool True if the string is a flag
     */
    private function isCommandParam(string $command): bool
    {
        return Str::startsWith($command, '-') || Str::startsWith($command, '--');
    }

    /**
     * Remove surrounding quotes (single or double) from a string.
     *
     * @param string $value Input string
     * @return string Unquoted string
     */
    private function removeQuote(string $value): string
    {
        return Str::match($value, '/(["\'])(.*?)\1/')[2] ?? $value;
    }

    /**
     * Retrieve the value of a parsed option by name.
     *
     * Returns default if the option is not set.
     *
     * @param string                              $name    Option name
     * @param string|string[]|bool|int|null       $default Default value
     * @return string|string[]|bool|int|null      Option value
     */
    protected function option(string $name, mixed $default = null): mixed
    {
        if (!array_key_exists($name, $this->optionMapper)) {
            return $default;
        }
        $option = $this->optionMapper[$name];
        if (is_array($option) && 1 === count($option)) {
            return $option[0];
        }

        return $option;
    }

    /**
     * Check if a specific option was provided.
     *
     * @param string $name Option name
     * @return bool True if the option exists
     */
    protected function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->optionMapper);
    }

    /**
     * Get positional arguments (non-named values).
     *
     * @return string[] Positional parameters
     */
    protected function optionPosition(): array
    {
        return $this->optionMapper[''];
    }

    /**
     * Magic getter for options via property access.
     *
     * @param string $name Option name
     * @return string|bool|int|null Option value
     */
    public function __get(string $name): mixed
    {
        return $this->option($name);
    }

    /**
     * Check if an option exists (ArrayAccess).
     *
     * @param mixed $offset Option name
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->optionMapper);
    }

    /**
     * Get the value of an option (ArrayAccess).
     *
     * @param mixed $offset Option name
     * @return mixed Option value
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->option($offset);
    }

    /**
     * Setting options is not allowed. Always throws an exception.
     *
     * @param mixed $offset Option name
     * @param mixed $value  Value to set
     * @throws Exception
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception('Command cant be modify');
    }

    /**
     * Unsetting options is not allowed. Always throws an exception.
     *
     * @param mixed $offset Option name
     * @throws Exception
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new Exception('Command cant be modify');
    }

    /**
     * Default execution method.
     *
     * Override this in subclasses to implement the command's behavior.
     *
     * @return void
     */
    public function main()
    {
    }
}
