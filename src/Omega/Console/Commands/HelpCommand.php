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

namespace Omega\Console\Commands;

use Omega\Console\Command;
use Omega\Console\Style\Style;
use Omega\Console\Traits\PrintHelpTrait;
use Omega\Console\Util;
use Omega\Application\Application;
use Omega\Console\CommandMap;
use Omega\Text\Str;

use function array_merge;
use function class_exists;
use function explode;
use function implode;
use function in_array;
use function method_exists;
use function Omega\Console\info;
use function Omega\Console\style;
use function Omega\Console\warn;
use function ucfirst;

/**
 * Class HelpCommand
 *
 * This class provides help-related functionalities for the console application.
 * It handles displaying general usage instructions, listing registered commands,
 * and showing help details for specific commands.
 *
 * Example usage:
 * ```php
 * php omega --help            # Show all help information
 * php omega --list            # List all registered commands with handler classes
 * php omega help make:command # Show help for a specific command
 * ```
 * @category   Omega
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
 * @property string[] $classNamespace  A list of namespaces where command classes may reside.
 * @property array<int, array<string, mixed>> $command  Command pattern registrations with respective handlers.
 * @property string $banner  ASCII-art logo printed in the help output.
 */
class HelpCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Static registration of command patterns and associated callback handlers.
     * Each item includes a command pattern and the function that handles it.
     *
     * Supported patterns:
     * - `--help`, `-h` → shows general help (calls `main`)
     * - `--list` → lists registered commands (calls `commandList`)
     * - `help` → shows help for a specific command (calls `commandHelp`)
     *
     * @var array<int, array{pattern: string|string[], fn: array{class-string, string}}>}
     */
    protected array $classNamespace = [];

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => ['-h', '--help'],
            'fn'      => [self::class, 'main'],
        ],
        [
            'pattern' => '--list',
            'fn'      => [self::class, 'commandList'],
        ],
        [
            'pattern' => 'help',
            'fn'      => [self::class, 'commandHelp'],
        ],
    ];

    /**
     * Print help metadata to be consumed by the core help handler.
     *
     * @return array{
     *     commands: array<string, string>,
     *     options: array<string, string>,
     *     relation: array<string, string[]>
     * }
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'help' => 'Get help for available command',
            ],
            'options'   => [],
            'relation'  => [
                'help' => ['[command_name]'],
            ],
        ];
    }

    /** @var string ASCII logo banner to print at the top of help output. */
    protected string $banner = '
  ___  __  __ _____ ____    _
 / _ \|  \/  | ____/ ___|  / \
| | | | |\/| |  _|| |  _  / _ \
| |_| | |  | | |__| |_| |/ ___ \
 \___/|_|  |_|_____\____/_/   \_\

';

    /**
     * Handles the `--help` and `-h` flags.
     * Prints general help output, including usage instructions, available flags,
     * commands, and options registered by all loaded command classes.
     *
     * @return int Returns 0 on success.
     */
    public function main(): int
    {
        $hasVisited      = [];
        $this->printHelp = [
            'margin-left'         => 8,
            'column-1-min-length' => 16,
        ];

        foreach ($this->commandMaps() as $command) {
            $class = $command->class();
            if (!in_array($class, $hasVisited)) {
                $hasVisited[] = $class;

                if (class_exists($class)) {
                    $class = new $class([], $command->defaultOption());

                    if (!method_exists($class, 'printHelp')) {
                        continue;
                    }

                    $help = app()->call([$class, 'printHelp']) ?? [];

                    /** @noinspection PhpConditionAlreadyCheckedInspection */
                    if (isset($help['commands']) && $help['commands'] !== null) {
                        foreach ($help['commands'] as $command => $desc) {
                            $this->commandDescribes[$command] = $desc;
                        }
                    }

                    /** @noinspection PhpConditionAlreadyCheckedInspection */
                    if (isset($help['options']) && $help['options'] !== null) {
                        foreach ($help['options'] as $option => $desc) {
                            $this->optionDescribes[$option] = $desc;
                        }
                    }

                    if (isset($help['relation']) && $help['relation'] != null) {
                        foreach ($help['relation'] as $option => $desc) {
                            $this->commandRelation[$option] = $desc;
                        }
                    }
                }
            }
        }

        $printer = new Style();
        $printer->push($this->banner)->textGreen();
        $printer
            ->newLines(2)
            ->push('Usage:')
            ->newLines(2)->tabs()
            ->push('php')->textGreen()
            ->push(' omega [flag]')
            ->newLines()->tabs()
            ->push('php')->textGreen()
            ->push(' omega [command] ')
            ->push('[option]')->textDim()
            ->newLines(2)

            ->push('Available flag:')
            ->newLines(2)->tabs()
            ->push('--help')->textDim()
            ->tabs(3)
            ->push('Get all help commands')
            ->newLines()->tabs()
            ->push('--list')->textDim()
            ->tabs(3)
            ->push('Get list of commands registered (class & function)')
            ->newLines(2)
        ;

        $printer->push('Available command:')->newLines(2);
        $printer = $this->printCommands($printer)->newLines();

        $printer->push('Available options:')->newLines();
        $printer = $this->printOptions($printer);

        $printer->out();

        return 0;
    }

    /**
     * Handles the `--list` flag.
     * Lists all registered commands with the associated class and method.
     * Useful for debugging or CLI introspection.
     *
     * Example output:
     *   make:controller   App\Console\MakeCommand .... makeController
     *   db:seed           App\Console\DBCommand .... seedDatabase
     *
     * @return int Returns 0 on success.
     */
    public function commandList(): int
    {
        style('List of all command registered:')->out();

        $maks1    = 0;
        $maks2    = 0;
        $commands = $this->commandMaps();
        foreach ($commands as $command) {
            $option = array_merge($command->cmd(), $command->patterns());
            $length = Str::length(implode(', ', $option));

            if ($length > $maks1) {
                $maks1 = $length;
            }

            $length = Str::length($command->class());
            if ($length > $maks2) {
                $maks2 = $length;
            }
        }

        foreach ($commands as $command) {
            $option = array_merge($command->cmd(), $command->patterns());
            style(implode(', ', $option))->textLightYellow()->out(false);

            $length1 = Str::length(implode(', ', $option));
            $length2 = Str::length($command->class());
            style('')
                ->repeat(' ', $maks1 - $length1 + 4)
                ->push($command->class())->textGreen()
                ->repeat('.', $maks2 - $length2 + 8)->textDim()
                ->push($command->method())
                ->out();
        }

        return 0;
    }

    /**
     * Handles the `help <command>` pattern.
     * Resolves the command name, searches known namespaces, and prints
     * the specific help metadata defined in the target class's `printHelp()` method.
     *
     * @return int Returns 0 if help found, 1 if command not found or invalid.
     */
    public function commandHelp(): int
    {
        if (!isset($this->option[0])) {
            style('')
                ->tap(info('To see help command, place provide command_name'))
                ->textYellow()
                ->push('php omega help <command_name>')->textDim()
                ->newLines()
                ->push('              ^^^^^^^^^^^^')->textRed()
                ->out()
            ;

            return 1;
        }

        $className = $this->option[0];
        if (Str::contains(':', $className)) {
            $className = explode(':', $className);
            $className = $className[0];
        }

        $className .= 'Command';
        $className  = ucfirst($className);
        $namespaces = array_merge(
            $this->classNamespace,
            [
                'App\\Commands\\',
                'Omega\\Console\\Commands\\',
            ]
        );

        foreach ($namespaces as $namespace) {
            $class_name = $namespace . $className;
            if (class_exists($class_name)) {
                $class = new $class_name([]);

                $help = app()->call([$class, 'printHelp']) ?? [];

                if (isset($help['commands']) && $help['commands'] != null) {
                    $this->commandDescribes = $help['commands'];
                }

                if (isset($help['options']) && $help['options'] != null) {
                    $this->optionDescribes = $help['options'];
                }

                if (isset($help['relation']) && $help['relation'] != null) {
                    $this->commandRelation = $help['relation'];
                }

                style('Available command:')->newLines()->out();
                $this->printCommands(new Style())->out();

                style('Available options:')->newLines()->out();
                $this->printOptions(new Style())->out();

                return 0;
            }
        }

        warn("Help for `{$this->option[0]}` command not found")->out(false);

        return 1;
    }

    /**
     * Loads and returns all registered command mappings from the application config.
     *
     * @return CommandMap[] An array of CommandMap instances representing all known commands.
     */
    private function commandMaps(): array
    {
        return Util::loadCommandFromConfig(Application::getInstance());
    }
}
