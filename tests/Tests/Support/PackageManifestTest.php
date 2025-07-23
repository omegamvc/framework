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

use Omega\Support\PackageManifest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_exists;
use function unlink;

use const DIRECTORY_SEPARATOR;

/**
 * Unit tests for the PackageManifest class.
 *
 * This test suite validates the functionality of the PackageManifest system,
 * ensuring it correctly builds and retrieves a cached service provider manifest
 * from Composer-installed packages. It also confirms the ability to extract
 * provider configurations from the generated manifest.
 *
 * Key test coverage includes:
 * - Manifest file creation
 * - Manifest file deletion (cleanup)
 * - Extraction of service providers from packages
 * - Internal config resolution
 *
 * @category  Omega\Tests
 * @package   Support
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(PackageManifest::class)]
class PackageManifestTest extends TestCase
{
    /**
     * The base path of the application.
     *
     * This is the root directory where the application files are located,
     * typically used to resolve relative paths for dependencies and vendor packages.
     *
     * @var string
     */
    private string $basePath = '';

    /**
     * The path to the application's cache directory.
     *
     * This directory is where cached configuration files, including
     * the generated package manifest, are stored.
     *
     * @var string
     */
    private string $applicationCachePath = '';

    /**
     * The full path to the generated package manifest file.
     *
     * This file is produced by the PackageManifest class and stores
     * the discovered service providers from installed Composer packages.
     *
     * @var string
     */
    private string $packageManifest = '';

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
        $this->deleteAsset();

        $this->basePath             = dirname(__DIR__) . '/fixtures/support/app1/';
        $this->applicationCachePath = dirname(__DIR__) . '/fixtures/support/app1/bootstrap/cache/';
        $this->packageManifest      = dirname(__DIR__) . '/fixtures/support/app1/bootstrap/cache/packages.php';
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
        $this->deleteAsset();
    }

    /**
     * Delete the cached package manifest file if it exists.
     *
     * This method is used in test setup and teardown to ensure
     * a clean test environment by removing any previously generated
     * package manifest files from the filesystem.
     *
     * @return void
     */
    public function deleteAsset(): void
    {
        if (file_exists($this->packageManifest)) {
            @unlink($this->packageManifest);
        }
    }

    /**
     * Test it can build.
     *
     * @return void
     */
    public function testItCanBuild(): void
    {
        $package_manifest = new PackageManifest($this->basePath, $this->applicationCachePath, DIRECTORY_SEPARATOR . 'package' . DIRECTORY_SEPARATOR);
        $package_manifest->build();

        $this->assertTrue(file_exists($this->packageManifest));
    }

    /**
     * Test it can get package manifest.
     *
     * @return void
     */
    public function testItCanGetPackageManifest(): void
    {
        $package_manifest = new PackageManifest($this->basePath, $this->applicationCachePath, DIRECTORY_SEPARATOR . 'package' . DIRECTORY_SEPARATOR);

        $manifest = (fn () => $this->{'getPackageManifest'}())->call($package_manifest);

        $this->assertEquals([
            'packages/package1' => [
                'providers' => [
                    'Package//Package1//ServiceProvider::class',
                ],
            ],
            'packages/package2' => [
                'providers' => [
                    'Package//Package2//ServiceProvider::class',
                    'Package//Package2//ServiceProvider2::class',
                ],
            ],
        ], $manifest);
    }

    /**
     * Test it can get config.
     *
     * @return void
     */
    public function testItCanGetConfig(): void
    {
        $package_manifest = new PackageManifest($this->basePath, $this->applicationCachePath, DIRECTORY_SEPARATOR . 'package' . DIRECTORY_SEPARATOR);

        $config = (fn () => $this->{'config'}('providers'))->call($package_manifest);

        $this->assertEquals([
            'Package//Package1//ServiceProvider::class',
            'Package//Package2//ServiceProvider::class',
            'Package//Package2//ServiceProvider2::class',
        ], $config);
    }

    /**
     * Test it can get providers.
     *
     * @¶eturn void
     */
    public function testItCanGetProviders(): void
    {
        $package_manifest = new PackageManifest($this->basePath, $this->applicationCachePath, DIRECTORY_SEPARATOR . 'package' . DIRECTORY_SEPARATOR);

        $config = $package_manifest->providers();

        $this->assertEquals([
            'Package//Package1//ServiceProvider::class',
            'Package//Package2//ServiceProvider::class',
            'Package//Package2//ServiceProvider2::class',
        ], $config);
    }
}
