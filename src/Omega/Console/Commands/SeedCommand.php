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

use Exception;
use Omega\Console\Command;
use Omega\Console\Prompt;
use Omega\Console\Traits\PrintHelpTrait;
use Omega\Template\Generate;
use Omega\Template\Method;
use Throwable;

use function file_exists;
use function file_put_contents;
use function Omega\Console\fail;
use function Omega\Console\info;
use function Omega\Console\ok;
use function Omega\Console\style;
use function Omega\Console\warn;

/**
 * SeedCommand handles database seeding tasks from the command line.
 *
 * Available commands:
 *
 * - `db:seed`: Runs a specific or default seeder class.
 *     Examples:
 *     php omega db:seed --class=UserSeeder
 *     php omega db:seed --name-space=App\\Database\\Seeders\\Seeder
 *
 * - `make:seed`: Generates a new seeder class.
 *     Example:
 *     php omega make:seed ProductSeeder
 *
 * This class includes developer environment checks and supports interactive
 * confirmation when running in production.
 *
 * @category   Omega
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
 * @property string|null $class
 * @property bool|null $force
 */
class SeedCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Registered command patterns and their associated handlers.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'db:seed',
            'fn'     => [self::class, 'main'],
        ], [
            'pattern' => 'make:seed',
            'fn'      => [self::class, 'make'],
        ],
    ];

    /**
     * Returns help metadata for available commands and options.
     *
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'db:seed'   => 'Run seeding',
                'make:seed' => 'Create new seeder class',
            ],
            'options'   => [
                '--class'      => 'Target class (will add `Database\\Seeders\\`)',
                '--name-space' => 'Target class with full namespace',
            ],
            'relation'  => [
                'db:seed' => ['--class', '--name-space'],
            ],
        ];
    }

    /**
     * Prompts the user for confirmation if the app is not in development mode.
     *
     * @return bool True if allowed to run, false otherwise.
     * @throws Exception If the prompt fails.
     */
    private function runInDev(): bool
    {
        if (app()->isDev() || $this->force) {
            return true;
        }

        /* @var bool */
        return (new Prompt(style('Running seeder in production?')->textRed(), [
            'yes' => fn () => true,
            'no'  => fn () => false,
        ], 'no'))
            ->selection([
                style('yes')->textDim(),
                ' no',
            ])
            ->option();
    }

    /**
     * Executes a seeder class.
     *
     * Logic:
     * - Resolves target class from --class or --name-space.
     * - Uses default seeder if neither is specified.
     * - Validates class existence.
     * - Calls the `run()` method on the seeder.
     *
     * @return int Exit code: 0 (success), 1 (failure), 2 (aborted).
     * @throws Exception If the environment prompt fails or seeder execution throws.
     */
    public function main(): int
    {
        $class     = $this->class;
        $namespace = $this->option('name-space');
        $exit      = 0;

        if (false === $this->runInDev()) {
            return 2;
        }

        if (null !== $class && null !== $namespace) {
            warn('Use only one class or namespace, be specific.')->out(false);

            return 1;
        }

        if (null === $class && null !== $namespace) {
            $class = $namespace;
        }

        if ($class !== null && null === $namespace) {
            $class = 'Database\\Seeders\\' . $class;
        }

        if (null === $class && null === $namespace) {
            $class = 'Database\\Seeders\\DatabaseSeeder';
        }

        if (false === class_exists($class)) {
            /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
            warn("Class '{$class}::class' doest exist.")->out(false);

            return 1;
        }

        info('Running seeders...')->out(false);
        try {
            app()->call([$class, 'run']);

            ok('Success run seeder ' . $class)->out(false);
        } catch (Throwable $th) {
            warn($th->getMessage())->out(false);
            $exit = 1;
        }

        return $exit;
    }

    /**
     * Generates a new seeder class in the `Database\Seeders` namespace.
     *
     * Validates:
     * - That the class name is provided.
     * - That the file doesn't already exist (unless forced).
     *
     * @return int Exit code: 0 (success), 1 (error).
     */
    public function make(): int
    {
        $class = $this->option[0] ?? null;

        if (null === $class) {
            warn('command make:seed require class name')->out(false);

            return 1;
        }

        if (file_exists(seeder_path() . $class . '.php') && !$this->force) {
            /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
            warn("Class '{$class}::class' already exist.")->out(false);

            return 1;
        }

        $make = new Generate($class);
        $make->tabIndent(' ');
        $make->tabSize(4);
        $make->namespace('Database\Seeders');
        $make->use('Omega\Database\Seeder\Seeder');
        $make->extend('Seeder');
        $make->setEndWithNewLine();
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        $make->addMethod('run')
            ->visibility(Method::PUBLIC_)
            ->setReturnType('void')
            ->body('// run some insert db');

        if (file_put_contents(seeder_path() . $class . '.php', $make->__toString())) {
            ok('Success create seeder')->out();

            return 0;
        }

        fail('Fail to create seeder')->out();

        return 1;
    }
}
