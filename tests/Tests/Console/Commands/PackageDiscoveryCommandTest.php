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

use Omega\Console\Commands\PackageDiscoveryCommand;
use Omega\Application\Application;
use Omega\Support\PackageManifest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for the `PackageDiscoveryCommand` class.
 *
 * This test verifies that the `discovery()` method correctly generates the
 * `packages.php` file in the application's bootstrap cache directory.
 *
 * It ensures that:
 * - A valid `Application` instance can be passed to the command.
 * - A custom `PackageManifest` can be injected into the application container.
 * - The discovery process creates the expected file.
 * - The command returns a successful exit code (0).
 *
 * The generated `packages.php` file is removed after each test to maintain
 * a clean and isolated environment.
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
#[CoversClass(PackageDiscoveryCommand::class)]
class PackageDiscoveryCommandTest extends TestCase
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
        if (file_exists($file = dirname(__DIR__, 2) . '/fixtures/console/app/bootstrap/cache/packages.php')) {
            @unlink($file);
        }
    }

    /**
     * Test it can create config file.
     *
     * @return void
     */
    public function testItCanCreateConfigFile(): void
    {
        $app = new Application(dirname(__DIR__, 2) . '/fixtures/console/app/');

        // overwrite PackageManifest has been set in Application before.
        $app->set(PackageManifest::class, fn () => new PackageManifest(
            basePath: $app->getBasePath(),
            applicationCachePath: $app->getApplicationCachePath(),
            vendorPath: '/package/'
        ));

        $discovery = new PackageDiscoveryCommand(['omega', 'package:discovery']);
        ob_start();
        $out = $discovery->discovery($app);
        ob_get_clean();

        $this->assertEquals(0, $out);

        $app->flush();
    }
}
