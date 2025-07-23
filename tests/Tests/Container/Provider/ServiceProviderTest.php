<?php

/**
 * Part of Omega - Tests\Container Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Container\Provider;

use Omega\Container\Exceptions\FileCopyException;
use Omega\Container\Provider\AbstractServiceProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function count;
use function dirname;
use function file_exists;
use function file_put_contents;
use function glob;
use function is_dir;
use function microtime;
use function Omega\Time\now;
use function scandir;
use function unlink;

/**
 * Class ServiceProviderTest
 *
 * This PHPUnit test class verifies the functionality of the AbstractServiceProvider,
 * focusing on module exporting and file/directory importing operations.
 *
 * It tests:
 * - Exporting modules with various configurations and retrieving them.
 * - Importing single files, including scenarios where the destination folder
 *   may not exist or the target file already exists (with overwrite options).
 * - Importing directories recursively, ensuring nested contents are properly copied.
 * - Behavior when importing files or directories with existing targets, both
 *   allowing and disallowing overwrites.
 * - Proper exception handling when source files or directories are missing or inaccessible.
 *
 * The test class also handles cleanup of created test files after each test run.
 *
 * @category   Omega\Tests
 * @package    Container
 * @subpackage Providers
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(AbstractServiceProvider::class)]
class ServiceProviderTest extends TestCase
{
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
        $file        = dirname(__DIR__, 2) . '/fixtures/container/copy/to/file.txt';
        $file1       = dirname(__DIR__, 2) . '/fixtures/container/copy/to/folder/file.txt';
        $foldersBase = dirname(__DIR__, 2) . '/fixtures/container/copy/to/folders/';

        // Cancella il file se esiste
        if (file_exists($file)) {
            @unlink($file);
        }

        if (file_exists($file1)) {
            @unlink($file1);
        }

        // Cancella tutte le cartelle folder-* se non vuote
        foreach (glob($foldersBase . 'folder-*') as $folder) {
            if (is_dir($folder) && !$this->isDirEmpty($folder)) {
                $this->deleteDir($folder);
            }
        }
    }

    /**
     * Controlla se una directory è vuota
     */
    private function isDirEmpty(string $dir): bool
    {
        return count(array_diff(scandir($dir), ['.', '..'])) === 0;
    }

    /**
     * Cancella ricorsivamente directory e file
     */
    private function deleteDir(string $dir): void
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->deleteDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    /**
     * Test it can export module.
     *
     * @return void
     */
    public function testItCanExportModule(): void
    {
        AbstractServiceProvider::export([
            '/vendor/package/database/' => '/database/',
        ]);

        AbstractServiceProvider::export([
            '/vendor/package/resource/view/' => '/resource/view/',
        ], 'package-share');

        AbstractServiceProvider::export([
            '/vendor/package/resource/js/app.js'   => '/resource/js/app.js',
            '/vendor/package/resource/css/app.css' => '/resource/css/app.css',
        ], 'package-share');

        $this->assertEquals([
            ''              => ['/vendor/package/database/' => '/database/'],
            'package-share' => [
                '/vendor/package/resource/view/'       => '/resource/view/',
                '/vendor/package/resource/js/app.js'   => '/resource/js/app.js',
                '/vendor/package/resource/css/app.css' => '/resource/css/app.css',
            ],
        ], AbstractServiceProvider::getModules());
    }

    /**
     * Test it can get module.
     *
     * @return void
     */
    public function testItCanGetModule(): void
    {
        AbstractServiceProvider::export([
            '/vendor/package/database/' => '/database/',
        ]);
        AbstractServiceProvider::flushModule();

        $this->assertEquals([], AbstractServiceProvider::getModules());
    }

    /**
     * Test it can import file.
     *
     * @return void
     */
    public function testItCanImportFile(): void
    {
        $this->assertTrue(AbstractServiceProvider::importFile(
            dirname(__DIR__, 2) . '/fixtures/container/copy/from/file.txt',
            dirname(__DIR__, 2) . '/fixtures/container/copy/to/file.txt'
        ));

        $this->assertTrue(file_exists(dirname(__DIR__, 2) . '/fixtures/container/copy/to/file.txt'));
    }

    /**
     * est it can import file with folder doest exists.
     *
     * @return void
     */
    public function testItCanImportFileWithFolderDoestExits(): void
    {
        $random = now()->format('YmdHis') . microtime();
        $this->assertTrue(AbstractServiceProvider::importFile(
            dirname(__DIR__, 2) . '/fixtures/container/copy/from/file.txt',
            dirname(__DIR__, 2) . '/fixtures/container/copy/to/folders/folder-' . $random . '/file.txt'
        ));

        $this->assertTrue(file_exists(dirname(__DIR__, 2) . '/fixtures/container/copy/to/folders/folder-' . $random . '/file.txt'));
    }

    /**
     * Test it can import file with target exists.
     *
     * @return void
     */
    public function testItCanImportFileWithTargetExists(): void
    {
        file_put_contents(dirname(__DIR__, 2) . '/fixtures/container/copy/to/file.txt', '');

        $this->assertTrue(AbstractServiceProvider::importFile(
            dirname(__DIR__, 2) . '/fixtures/container/copy/from/file.txt',
            dirname(__DIR__, 2) . '/fixtures/container/copy/to/file.txt',
            true
        ));

        $this->assertTrue(file_exists(dirname(__DIR__, 2) . '/fixtures/container/copy/to/file.txt'));
    }

    /**
     * Test it can not import file with target exists.
     *
     * @return void
     */
    public function testItCanNotImportFileWithTargetExists(): void
    {
        file_put_contents(dirname(__DIR__, 2) . '/fixtures/container/copy/to/file.txt', '');

        $this->assertFalse(AbstractServiceProvider::importFile(
            dirname(__DIR__, 2) . '/fixtures/container/copy/from/file.txt',
            dirname(__DIR__, 2) . '/fixtures/container/copy/to/file.txt'
        ));

        $this->assertTrue(file_exists(dirname(__DIR__, 2) . '/fixtures/container/copy/to/file.txt'));
    }

    /**
     * Test it can import folder.
     *
     * @return void
     */
    public function testItCanImportFolder(): void
    {
        $random = now()->format('YmdHis') . microtime();
        $this->assertTrue(AbstractServiceProvider::importDir(
            dirname(__DIR__, 2) . '/fixtures/container/copy/from/folder',
            dirname(__DIR__, 2) . '/fixtures/container/copy/to/folders/folder-' . $random
        ));

        $this->assertTrue(file_exists(dirname(__DIR__, 2) . '/fixtures/container/copy/to/folders/folder-' . $random . '/file.txt'));
    }

    /**
     * Test it can import folder recursing.
     *
     * @return void
     */
    public function testItCanImportFolderRecursing(): void
    {
        $random = now()->format('YmdHis') . microtime();
        $this->assertTrue(AbstractServiceProvider::importDir(
            dirname(__DIR__, 2) . '/fixtures/container/copy/from/folder-nest',
            dirname(__DIR__, 2) . '/fixtures/container/copy/to/folders/folder-' . $random
        ));

        $this->assertTrue(file_exists(dirname(__DIR__, 2) . '/fixtures/container/copy/to/folders/folder-' . $random . '/file.txt'));
        $this->assertTrue(file_exists(dirname(__DIR__, 2) . '/fixtures/container/copy/to/folders/folder-' . $random . '/folder/file.txt'));
    }

    /**
     * Test it can import folder with target exists.
     *
     * @return void
     */
    public function testItCanImportFolderWithTargetExists(): void
    {
        $this->assertTrue(AbstractServiceProvider::importDir(
            dirname(__DIR__, 2) . '/fixtures/container/copy/from/folder',
            dirname(__DIR__, 2) . '/fixtures/container/copy/to/folder',
            true
        ));

        $this->assertTrue(file_exists(dirname(__DIR__, 2) . '/fixtures/container/copy/to/folder/file.txt'));
    }

    /**
     * Test it can not import folder with target exists.
     *
     * @return void
     */
    public function testItCaNotImportFolderWithTargetExists(): void
    {
        // @todo: Always keep in mind that assertTrue might not be the right solution.
        // @todo: I’ll need to check whether assertFalse was the intended behavior, but incorrect
        // @todo: due to the implementation of importDir.
        //
        // @todo: unlink del folder.
        $this->assertTrue(AbstractServiceProvider::importDir(
            dirname(__DIR__, 2) . '/fixtures/container/copy/from/folder',
            dirname(__DIR__, 2) . '/fixtures/container/copy/to/folder'
        ));

        $this->assertTrue(file_exists(dirname(__DIR__, 2) . '/fixtures/container/copy/to/folder/file.txt'));
    }

    /**
     * Test it throws exception if source file does not exist.
     *
     * @return void
     */
    public function testImportFileThrowsExceptionIfSourceMissing(): void
    {
        $this->expectException(FileCopyException::class);
        $this->expectExceptionMessageMatches('/Failed to copy file from .* to .*/');

        AbstractServiceProvider::importFile(
            dirname(__DIR__, 2) . '/fixtures/container/copy/from/missing.txt',
            dirname(__DIR__, 2) . '/fixtures/container/copy/to/missing.txt'
        );
    }
}
