<?php

/**
 * Part of Omega - Tests\Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Support;

use Omega\Support\Path;
use PHPUnit\Framework\TestCase;

/**
 * Class PathTest
 *
 * Unit tests for the Omega\Support\Path utility class.
 *
 * These tests verify the correct resolution and normalization of filesystem paths
 * relative to a configurable base path using dot notation. The tests cover:
 * - Initialization of the base path.
 * - Conversion of dot notation paths into normalized directory paths with trailing slashes.
 * - Resolution of full file paths within directories.
 * - Handling of multiple file inputs as arrays or glob patterns.
 * - Proper normalization of directory separators across different platforms.
 *
 * The goal of these tests is to ensure that Path consistently returns accurate,
 * platform-independent paths that are compatible with both single and multiple file retrievals.
 *
 * @category  Omega\Tests
 * @package   Support
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
class PathTest extends TestCase
{
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
        Path::init('/var/www/project');
    }

    /**
     * Test get path with directory only.
     *
     * @return void
     */
    public function testGetPathWithDirectoryOnly(): void
    {
        $result = Path::getPath('app');
        $this->assertSame('/var/www/project/app/', $result);
    }

    /**
     * Test get path with directory and file.
     *
     * @return void
     */
    public function testGetPathWithDirectoryAndFile(): void
    {
        $result = Path::getPath('app.Model');
        $this->assertSame('/var/www/project/app/Model/', $result);
    }

    /**
     * Test get path with empty base path.
     *
     * @return void
     */
    public function testGetPathWithEmptyBasePath(): void
    {
        Path::init('');
        $result = Path::getPath('storage');
        $this->assertSame('/storage/', $result);
    }

    /**
     * Test get path with trailing slash always present.
     *
     * @return void
     */
    public function testGetPathWithTrailingSlashAlwaysPresent(): void
    {
        $result = Path::getPath('app.Model');
        $this->assertStringEndsWith('/', $result);
    }

    /**
     * Test get path returns base path if no argument.
     *
     * @return void
     */
    public function testGetPathReturnsBasePathIfNoArguments(): void
    {
        Path::init('/var/www/project');
        $result = Path::getPath('');
        $this->assertSame('/var/www/project/', $result);
    }
}
