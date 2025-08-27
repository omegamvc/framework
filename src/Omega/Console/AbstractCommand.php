<?php

declare(strict_types=1);

namespace Omega\Console;

use ArrayAccess;
use Exception;
use Omega\Console\Traits\TerminalTrait;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
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
 * Add customize terminal style by adding traits:
 * - TraitCommand (optional).
 *
 * @property string $_ Get argument name
 *
 * @implements ArrayAccess<string, string|bool|int|null>
 */
abstract class AbstractCommand implements ArrayAccess, CommandInterface
{
    use TerminalTrait;

    /** @var string|array<int, string> Commandline input. */
    protected string|array $cmd;

    /** @var array<int, string> Command line input. */
    protected array $option;

    /** @var array<string, string|string[]|bool|int|null> Option object mapper. */
    protected array $optionMapper;

    /** @var array<string, string> Option describe for input. */
    protected array $commandDescribes = [];

    /** @var array<string, string> Option describe for print. */
    protected array $optionDescribes = [];

    /** @var array<string, array<int, string>> Relation between Option and Argument. */
    protected array $commandRelation = [];

    /**
     * Parse commandline.
     *
     * @param array<int, string>                  $argv
     * @param array<string, string|bool|int|null> $defaultOption
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
     * parse option to readable array option.
     *
     * @param array<int, string|bool|int|null> $argv Option to parse
     * @return array<string, string|bool|int|null>
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
                $name     = preg_replace('/^(-{1,2})/', '', $keyValue[0]);

                // alias check
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

                $next           = $argv[$nextKey];
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
     * Detect string is command or value.
     *
     * @param string $command
     * @return bool
     */
    private function isCommandParam(string $command): bool
    {
        return Str::startsWith($command, '-') || Str::startsWith($command, '--');
    }

    /**
     * Remove quote single or double.
     *
     * @param string $value
     * @return string
     */
    private function removeQuote(string $value): string
    {
        return Str::match($value, '/(["\'])(.*?)\1/')[2] ?? $value;
    }

    /**
     * Get parse commandline parameters (name, value).
     *
     * @param string $name
     * @param bool|int|string|string[]|null $default Default if parameter not found
     * @return string|string[]|bool|int|null
     */
    protected function option(string $name, array|bool|int|string|null $default = null): array|bool|int|string|null
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
     * Garantisce che la directory esista.
     *
     * @param string $binding Il binding nel container (es: "app.Http.Middlewares").
     * @return string Il path assoluto creato/verificato.
     */
    /**
     * @param string $binding
     * @return string
     * @throws InvalidDefinitionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function isPath(string $binding): string
    {
        // Recupera il path logico dal container
        $logicalPath = get_path($binding);

        // Normalizza in un path di filesystem
        $realPath = str_replace(['.', '/','\\'], DIRECTORY_SEPARATOR, $logicalPath);

        // Se non esiste, crea ricorsivamente
        if (!is_dir($realPath)) {
            mkdir($realPath, 0755, true);
        }

        return $realPath;
    }

    /**
     * Get parse commandline parameters (name, value).
     *
     * @param string $name
     * @return string|bool|int|null
     */
    public function __get(string $name): string|bool|int|null
    {
        return $this->option($name);
    }

    /**
     * @param mixed $offset — Check parse commandline parameters
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->optionMapper);
    }

    /**
     * @param mixed $offset — Check parse commandline parameters
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): string|int|bool|array|null
    {
        return $this->option($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     * @throws Exception
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception('Command cant be modify');
    }

    /**
     * @param mixed $offset
     * @return void
     * @throws Exception
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new Exception('Command cant be modify');
    }

    public function main(): int
    {
        return 0;
    }
}
