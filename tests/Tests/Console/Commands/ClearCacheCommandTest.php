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

use Omega\Cache\Cache;
use Omega\Cache\Storage\ArrayStorage;
use Omega\Console\Commands\ClearCacheCommand;
use Omega\Application\Application;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function ob_get_clean;
use function ob_start;

/**
 * Unit tests for the ClearCacheCommand.
 *
 * This test class verifies the behavior of the `cache:clear` console command,
 * including clearing the default cache driver, all drivers, specific drivers,
 * and handling cases when the cache is not properly configured.
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
#[CoversClass(ClearCacheCommand::class)]
class ClearCacheCommandTest extends TestCase
{
    /**
     * The application instance used for testing.
     *
     * This provides access to the container and configured services
     * such as the cache manager.
     *
     * @var Application|null
     */
    private ?Application $app = null;

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
        $this->app = new Application(__DIR__);
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
        $this->app->flush();
        $this->app = null;
    }

    /**
     * Test fails when cache is not set.
     *
     * @return void
     */
    public function testFailsWhenCacheIsNotSet(): void
    {
        $command = new ClearCacheCommand(['omega', 'clear:cache']);

        ob_start();
        $code = $command->clear($this->app);
        $out  = ob_get_clean();

        $this->assertEquals(1, $code);
        $this->assertStringContainsString('Cache is not set yet.', $out);
    }

    /**
     * Test clears default cache driver.
     *
     * @return void
     */
    public function testClearsDefaultCacheDriver(): void
    {
        $this->app->set('cache', fn () => new Cache());
        $command = new ClearCacheCommand(['omega', 'clear:cache']);

        ob_start();
        $code = $command->clear($this->app);
        $out  = ob_get_clean();

        $this->assertEquals(0, $code);
        $this->assertStringContainsString('Done default cache driver has been cleared.', $out);
    }

    /**
     * Test clears all cache drivers.
     *
     * @return void
     */
    public function testClearsAllCacheDrivers(): void
    {
        $cacheManager = new Cache();
        $cacheManager->setDriver('array', new ArrayStorage());
        $this->app->set('cache', fn () => $cacheManager);
        $command = new ClearCacheCommand(['omega', 'clear:cache', '--all'], ['all' => true]);

        ob_start();
        $code = $command->clear($this->app);
        $out  = ob_get_clean();

        $this->assertEquals(0, $code);
        $this->assertStringContainsString("clear 'array' driver.", $out);
    }

    /**
     * Test clears specific cache driver by name.
     *
     * @return void
     */
    public function testClearsSpecificCacheDriverByName(): void
    {
        $cacheManager = new Cache();
        $cacheManager->setDriver('array', new ArrayStorage());
        $this->app->set('cache', fn () => $cacheManager);
        $command = new ClearCacheCommand(['omega', 'clear:cache', '--drivers array'], ['drivers' => 'array']);

        ob_start();
        $code = $command->clear($this->app);
        $out  = ob_get_clean();

        $this->assertEquals(0, $code);
        $this->assertStringContainsString("clear 'array' driver.", $out);
    }
}
