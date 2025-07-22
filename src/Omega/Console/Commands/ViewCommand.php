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
use Omega\Console\Style\Decorate;
use Omega\Console\Style\ProgressBar;
use Omega\Console\Traits\PrintHelpTrait;
use Omega\Text\Str;
use Omega\View\Templator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function array_key_exists;
use function arsort;
use function asort;
use function clearstatcache;
use function count;
use function filemtime;
use function fnmatch;
use function is_file;
use function microtime;
use function Omega\Console\exit_prompt;
use function Omega\Console\info;
use function Omega\Console\ok;
use function Omega\Console\style;
use function Omega\Console\warn;
use function round;
use function str_replace;
use function strlen;
use function unlink;
use function usleep;

/**
 * Handles view-related CLI commands such as caching, clearing, and watching view files.
 *
 * This command supports:
 * - Caching compiled templates with `view:cache`
 * - Clearing compiled view cache with `view:clear`
 * - Watching view files for changes and live recompiling with `view:watch`
 *
 * Example usage:
 * ```bash
 * php omega view:cache --prefix=*.blade.php
 * php omega view:clear
 * php omega view:watch
 * ```
 *
 * @example
 * $ php omega view:cache
 * > Success, 12 file compiled (120 ms)
 *
 * @example
 * $ php omega view:watch --prefix=*.tpl
 * > PRE-COMPILE.......... 10ms
 * > Watching for changes...
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
 * @property string|null $prefix
 */
class ViewCommand extends Command
{
    use PrintHelpTrait;

    /**
     * List of available CLI commands handled by this class.
     *
     * Each command includes:
     * - `pattern`: The command string
     * - `fn`: Callable method
     * - `default`: Default CLI options
     *
     * @var array<int, array{
     *     pattern: string,
     *     fn: array{class-string, string},
     *     default: array<string, mixed>
     * }>
     */
    public static array $command = [
        [
            'pattern' => 'view:cache',
            'fn'      => [ViewCommand::class, 'cache'],
            'default' => [
                'prefix' => '*.php',
            ],
        ],
        [
            'pattern' => 'view:clear',
            'fn'      => [ViewCommand::class, 'clear'],
            'default' => [
                'prefix' => '*.php',
            ],
        ],
        [
            'pattern' => 'view:watch',
            'fn'      => [ViewCommand::class, 'watch'],
            'default' => [
                'prefix' => '*.php',
            ],
        ],
    ];

    /**
     * Returns help text for all available view commands.
     *
     * Includes description of commands and supported options.
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
                'view:cache' => 'Create all templator template (optimize)',
                'view:clear' => 'Clear all cached view file',
                'view:watch' => 'Watch all view file',
            ],
            'options'   => [
                '--prefix' => 'Finding file by pattern given',
            ],
            'relation'  => [
                'view:cache' => ['--prefix'],
                'view:clear' => ['--prefix'],
                'view:watch' => ['--prefix'],
            ],
        ];
    }

    /**
     * Find files in a directory recursively that match a filename pattern.
     *
     * @param string $directory Root path to search in.
     * @param string $pattern   Filename pattern (e.g., '*.php').
     * @return array<int, string> Full path of matched files.
     */
    private function findFiles(string $directory, string $pattern): array
    {
        $files    = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && fnmatch($pattern, $file->getFilename())) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Compiles and caches all view templates matching the prefix pattern.
     *
     * Displays a progress bar and a success message on completion.
     *
     * @param Templator $templator View templating engine.
     * @return int Exit code (0 = success, 1 = no files found).
     */
    public function cache(Templator $templator): int
    {
        $files = $this->findFiles(view_path(), $this->prefix);
        if ([] === $files) {
            return 1;
        }
        info('build compiler cache')->out(false);
        $count     = 0;
        $progress = new ProgressBar(':progress :percent - :current', [
            ':current' => fn ($current, $max): string => array_key_exists($current, $files) ? Str::replace($files[$current], view_path(), '') : '',
        ]);

        $progress->maks = count($files);
        $watchStart     = microtime(true);
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = Str::replace($file, view_path(), '');
                $templator->compile($filename);
                $count++;
            }
            $progress->current++;
            $time                = round(microtime(true) - $watchStart, 3) * 1000;
            $progress->complete = static fn (): string => (string) ok("Success, {$count} file compiled ({$time} ms).");
            $progress->tick();
        }

        return 0;
    }

    /**
     * Clears all cached compiled view files matching the given prefix.
     *
     * Shows a progress bar and success/failure messages.
     *
     * @return int Exit code (0 = success, 1 = no files cleared).
     */
    public function clear(): int
    {
        warn('Clear cache file in ' . cache_path())->out(false);
        $files = $this->findFiles(cache_path() . DIRECTORY_SEPARATOR, $this->prefix);

        if (0 === count($files)) {
            warn('No file cache clear.')->out();

            return 1;
        }

        $count     = 0;
        $progress = new ProgressBar(':progress :percent - :current', [
            ':current' => fn ($current, $max): string => array_key_exists($current, $files) ? Str::replace($files[$current], view_path(), '') : '',
        ]);

        $progress->maks = count($files);
        $watchStart     = microtime(true);
        foreach ($files as $file) {
            if (is_file($file)) {
                $count += unlink($file) ? 1 : 0;
            }
            $progress->current++;
            $time                = round(microtime(true) - $watchStart, 3) * 1000;
            $progress->complete = static fn (): string => (string) ok("Success, {$count} cache clear ({$time} ms).");
            $progress->tick();
        }

        return 0;
    }

    /**
     * Watches for changes in view files and recompiles them in real-time.
     *
     * Press any key to stop the watcher. Handles dependencies.
     *
     * @param Templator $templator View templating engine.
     * @return int Exit code (0 = success, 1 = no files found).
     * @throws Exception
     */
    public function watch(Templator $templator): int
    {
        warn('Clear cache file in ' . view_path() . $this->prefix)->out(false);


        /** @noinspection PhpUnusedLocalVariableInspection */
        $compiled   = [];
        $width      = $this->getWidth(40, 80);
        $signal     = false;
        $getIndexes = $this->getIndexFiles();
        if ([] === $getIndexes) {
            return 1;
        }

        // register ctrl+c
        exit_prompt('Press any key to stop watching', [
            'yes' => static function () use (&$signal) {
                $signal = true;
            },
        ]);

        // precompile
        $compiled = $this->precompile($templator, $getIndexes, $width);

        // watch file change until signal
        do {
            $reindex = false;
            foreach ($getIndexes as $file => $time) {
                clearstatcache(true, $file);
                $now = filemtime($file);

                // compile only newest file
                if ($now > $time) {
                    $dependency = $this->compile($templator, $file, $width);
                    foreach ($dependency as $compile => $time) {
                        $compile                   = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $compile);
                        $compiled[$compile][$file] = $time;
                    }
                    $getIndexes[$file] = $now;
                    $reindex            = true;

                    // recompile dependent
                    if (isset($compiled[$file])) {
                        foreach ($compiled[$file] as $compile => $time) {
                            $this->compile($templator, $compile, $width);
                            $getIndexes[$compile] = $now;
                        }
                    }
                }
            }

            // reindexing
            if (count($getIndexes) !== count($new_indexes = $this->getIndexFiles())) {
                $getIndexes = $new_indexes;
                $compiled    = $this->precompile($templator, $getIndexes, $width);
            }
            if ($reindex) {
                asort($getIndexes);
            }

            usleep(1_000); // 1ms
        } while (!$signal);

        return 0;
    }

    /**
     * Indexes view files by last modified time.
     *
     * Used to detect which files need to be watched and recompiled.
     *
     * @return array<string, int> [file path => last modified timestamp]
     */
    private function getIndexFiles(): array
    {
        $files = $this->findFiles(view_path(), $this->prefix);

        if (empty($files)) {
            warn('Error finding view file(s).')->out();

            return [];
        }

        // indexing files (time modified)
        $indexes = [];
        foreach ($files as $file) {
            if (false === is_file($file)) {
                continue;
            }

            $indexes[$file] = filemtime($file);
        }

        // sort for newest file
        arsort($indexes);

        return $indexes;
    }

    /**
     * Compile a single view file and measure its performance.
     *
     * @param Templator $templator View engine instance.
     * @param string    $file_path Full path to the view file.
     * @param int       $width     Terminal width for formatting output.
     * @return array<string, int> Map of dependencies compiled.
     */
    private function compile(Templator $templator, string $file_path, int $width): array
    {
        $watchStart = microtime(true);
        $filename   = Str::replace($file_path, view_path(), '');

        $templator->compile($filename);

        $length            = strlen($filename);
        $executeTime       = round(microtime(true) - $watchStart, 3) * 1000;
        $executeTimeLength = strlen((string) $executeTime);

        style($filename)
            ->repeat('.', $width - $length - $executeTimeLength - 2)->textDim()
            ->push((string) $executeTime)
            ->push('ms')->textYellow()
            ->out();

        return $templator->getDependency($file_path);
    }

    /**
     * Precompile all view files in the index list.
     *
     * Collects dependency information for future change tracking.
     *
     * @param Templator $templator   View templator instance.
     * @param array<string, int> $get_indexes Files with their modification time.
     * @param int       $width       Terminal width for formatted output.
     * @return array<string, array<string, int>> [compiled file => [source => timestamp]]
     */
    private function precompile(Templator $templator, array $get_indexes, int $width): array
    {
        $compiled        = [];
        $watchStart     = microtime(true);
        foreach ($get_indexes as $file => $time) {
            $filename        = Str::replace($file, view_path(), '');
            $templator->compile($filename);
            foreach ($templator->getDependency($file) as $compile => $time) {
                $compile                   = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $compile);
                $compiled[$compile][$file] = $time;
            }
        }
        $executeTime       = round(microtime(true) - $watchStart, 3) * 1000;
        $executeTimeLength = strlen((string) $executeTime);
        style('PRE-COMPILE')
            ->bold()->rawReset([Decorate::RESET])->textYellow()
            ->repeat('.', $width - $executeTimeLength - 13)->textDim()
            ->push((string) $executeTime)
            ->push('ms')->textYellow()
            ->out();

        return $compiled;
    }
}
