<?php

/**
 * Part of Omega - Tests\Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Console\Commands;

use FilesystemIterator;
use Omega\Console\Commands\VendorImportCommand;
use Omega\Container\Provider\AbstractServiceProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function dirname;
use function is_dir;
use function is_file;
use function microtime;
use function Omega\Time\now;
use function ob_get_clean;
use function ob_start;
use function rmdir;
use function scandir;
use function unlink;

/**
 * Unit tests for the VendorImportCommand.
 *
 * This class tests the behavior of the vendor:import command,
 * including tag-specific imports and file copying. It ensures
 * that exported files and folders are properly handled during import.
 * Temporary files and directories are cleaned up after each test.
 *
 * @category   Omega\Tests
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(VendorImportCommand::class)]
class VendorImportCommandsTest extends TestCase
{
    /** @var string|null The root path to the fixtures directory used in tests. */
    private ?string $basePath;

    /**
     * Set up the test environment before each test.
     *
     * This method is called before each test method is run.
     * Override it to initialize objects, mock dependencies, or reset state.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->basePath = dirname(__DIR__, 2);
    }

    /**
     * Clean up the test environment after each test.
     *
     * This method flushes and resets the application container
     * to ensure a clean state between tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        AbstractServiceProvider::flushModule();

        @unlink($this->basePath . '/fixtures/console/copy/to/file.txt');

        $foldersPath = $this->basePath . '/fixtures/console/copy/to/folders';

        if (is_dir($foldersPath)) {
            foreach (scandir($foldersPath) as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $fullPath = $foldersPath . DIRECTORY_SEPARATOR . $item;

                if (is_dir($fullPath)) {
                    foreach (
                        new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($fullPath, FilesystemIterator::SKIP_DOTS),
                            RecursiveIteratorIterator::CHILD_FIRST
                        ) as $file
                    ) {
                        $file->isDir() ? @rmdir($file->getRealPath()) : @unlink($file->getRealPath());
                    }

                    @rmdir($fullPath);
                } elseif (is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }
        }
    }

    /**
     * Test it can import.
     *
     * @return void
     */
    public function testItCanImport(): void
    {
        $publish = new VendorImportCommand(['omega', 'vendor:import', '--tag=test'], [
            'force' => false,
        ]);
        $random = now()->format('YmdHis') . microtime();

        AbstractServiceProvider::export(
            path: [$this->basePath . '/fixtures/console/copy/from/file.txt' => $this->basePath . '/fixtures/console/copy/to/file.txt'],
            tag: 'test'
        );
        AbstractServiceProvider::export(
            path: [$this->basePath . '/fixtures/console/copy/from/folder' => $this->basePath . '/fixtures/console/copy/to/folders/folder-' . $random],
            tag: 'test'
        );

        ob_start();
        $exit = $publish->main();
        ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(file_exists($this->basePath . '/fixtures/console/copy/to/file.txt'));
        $this->assertTrue(file_exists($this->basePath . '/fixtures/console/copy/to/folders/folder-' . $random . '/file.txt'));
    }

    /**
     * Test it can import with tag.
     *
     * @return void
     */
    public function testItCanImportWithTag(): void
    {
        $publish = new VendorImportCommand(['omega', 'vendor:import', '--tag=test'], [
            'force' => false,
            'tag'   => 'test',
        ]);
        $random = now()->format('YmdHis') . microtime();

        AbstractServiceProvider::export(
            path: [$this->basePath . '/fixtures/console/copy/from/file.txt' => $this->basePath . '/fixtures/console/copy/to/file.txt'],
            tag: 'test'
        );
        AbstractServiceProvider::export(
            path: [$this->basePath . '/fixtures/console/copy/from/folder' => $this->basePath . '/fixtures/console/copy/to/folders/folder-' . $random],
            tag: 'vendor'
        );

        ob_start();
        $exit = $publish->main();
        ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(file_exists($this->basePath . '/fixtures/console/copy/to/file.txt'));
        $this->assertFalse(file_exists($this->basePath . '/fixtures/console/copy/to/folders/folder-' . $random . '/file.txt'));
    }
}
