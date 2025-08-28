<?php

declare(strict_types=1);

namespace Omega\Console\Commands;

use Omega\Console\AbstractCommand;
use Omega\Console\Style\ProgressBar;
use Omega\Console\Traits\PrintHelpTrait;
use Omega\Container\Provider\AbstractServiceProvider;
use function count;
use function is_dir;
use function Omega\Console\success;

/**
 * Command to import files or directories from vendor packages.
 *
 * @property bool   $force Whether to force the import, overwriting existing files.
 * @property string $tag   The tag to identify specific commands to run.
 */
class VendorImportCommand extends AbstractCommand
{
    use PrintHelpTrait;

    /**
     * Progress bar for tracking import status.
     */
    private ProgressBar $status;

    /**
     * Command registration details.
     *
     * @var array<int, array<string, mixed>>
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
     * Provides help information for the command.
     *
     * @return array<string, array<string, string|string[]>>
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
     */
    public function main(): int
    {
        $this->status = new ProgressBar();
        $this->importItem(AbstractServiceProvider::getModules());

        return 0;
    }

    /**
     * Import specified modules (files or directories).
     *
     * @param array<string, array<string, string>> $modules
     */
    protected function importItem(array $modules): void
    {
        $this->status->mask = count($modules);
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
            success('Done ')->push((string)$added)->textYellow()->push(' file/folder has been added.')->out(false);
        }
    }

    /**
     * Update the console with the progress bar status.
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
