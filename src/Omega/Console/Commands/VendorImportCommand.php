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
use Omega\Console\Style\ProgressBar;
use Omega\Console\Traits\PrintHelpTrait;
use Omega\Container\Provider\AbstractServiceProvider;

use function count;
use function is_dir;
use function Omega\Console\ok;

/**
 * Class VendorImportCommand
 *
 * Command to import vendor packages (files or directories) based on specified tags.
 * Utilizes a progress bar to track the import status and supports forced overwrites.
 *
 * Example usage:
 * ```
 * $command = new VendorImportCommand();
 * $command->main(); // Starts the import process for all modules
 * ```
 *
 * This command can be invoked via the CLI pattern `vendor:import` with optional flags:
 * - `--tag`: specify which tagged modules to import (default is '*', meaning all)
 * - `--force`: whether to overwrite existing files
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
 * @property bool   $force Whether to force the import, overwriting existing files.
 * @property string $tag   The tag to identify specific commands to run.
 */
class VendorImportCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Progress bar for tracking import status.
     *
     * @var ProgressBar
     */
    private ProgressBar $status;

    /**
     * Command registration details.
     * Defines the CLI command pattern, the callable handler, and default parameters.
     *
     * @var array<int, array<string, mixed>>
     *
     * @example
     * ```
     * VendorImportCommand::$command = [
     *   [
     *     'pattern' => 'vendor:import',
     *     'fn'      => [VendorImportCommand::class, 'main'],
     *     'default' => ['tag' => '*', 'force' => false],
     *   ],
     * ];
     * ```
     */
    public static array $command = [
        [
            'pattern' => 'vendor:import',
            'fn'      => [self::class, 'main'],
            'default' => [
                'tag'   => '*',
                'force' => false,
            ],
        ],
    ];

    /**
     * Provides help information for the command including available commands,
     * options, and their relations.
     *
     * @return array<string, array<string, string|string[]>>
     *
     * @example
     * ```
     * $help = $command->printHelp();
     * // Returns:
     * // [
     * //   'commands' => ['vendor:import' => 'Import package in vendor.'],
     * //   'options' => ['--tag' => 'Specify the tag to run specific commands.'],
     * //   'relation' => ['vendor:import' => ['--tag', '--force']],
     * // ]
     * ```
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'vendor:import' => 'Import package in vendor.',
            ],
            'options'   => [
                '--tag' => 'Specify the tag to run specific commands.',
            ],
            'relation'  => [
                'vendor:import' => ['--tag', '--force'],
            ],
        ];
    }

    /**
     * Main method to execute the import command.
     * Initializes the progress bar and triggers the import of all registered modules.
     *
     * @return int Returns 0 on success.
     *
     * @example
     * ```
     * $exitCode = $command->main();
     * if ($exitCode === 0) {
     *     echo "Import completed successfully.\n";
     * }
     * ```
     */
    public function main(): int
    {
        $this->status = new ProgressBar();
        $this->importItem(AbstractServiceProvider::getModules());

        return 0;
    }

    /**
     * Import specified modules (files or directories).
     * Iterates through modules matching the specified tag and imports them.
     *
     * @param array<string, array<string, string>> $modules
     *     Array where the key is the tag and value is an array mapping source paths to target paths.
     *
     * @return void
     *
     * @example
     * ```
     * $modules = [
     *   'core' => ['/path/src' => '/path/dest'],
     *   'extras' => ['/extra/src' => '/extra/dest'],
     * ];
     * $command->importItem($modules);
     * ```
     */
    protected function importItem(array $modules): void
    {
        $this->status->maks = count($modules);
        $current            = 0;
        $added              = 0;

        foreach ($modules as $tag => $module) {
            $current++;

            if ($tag === $this->tag || $this->tag === '*') {
                foreach ($module as $from => $to) {
                    $added++;
                    if (is_dir($from)) {
                        $status = AbstractServiceProvider::importDir($from, $to, $this->force);
                        $this->status($current, $status, $from, $to);

                        continue 2;
                    }

                    $status = AbstractServiceProvider::importFile($from, $to, $this->force);
                    $this->status($current, $status, $from, $to);
                }
            }
        }

        if ($current > 0) {
            ok('Done ')->push((string)$added)->textYellow()->push(' file/folder has been added.')->out(false);
        }
    }

    /**
     * Update the console with the progress bar status.
     * Advances the progress bar and prints the current copy operation status.
     *
     * @param int    $current The current step number in the progress.
     * @param bool   $success Whether the current import operation succeeded.
     * @param string $from    Source path being copied from.
     * @param string $to      Destination path being copied to.
     *
     * @return void
     *
     * @example
     * ```
     * $command->status(1, true, '/src/path', '/dest/path');
     * ```
     */
    protected function status(int $current, bool $success, string $from, string $to): void
    {
        if (false === $success) {
            return;
        }

        $this->status->current = $current;
        $this->status->tickWith(':progress :percent :status', [
            'status' => fn (int $current, int $max): string => "Copying file/directory from '$from' to '$to'.",
        ]);
    }
}
