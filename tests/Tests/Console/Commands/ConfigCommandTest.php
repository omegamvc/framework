<?php

/**
 * Part of Omega - Tests\Console\Commands Package
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

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Console\Commands\ConfigCommand;
use Omega\Application\Application;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_exists;
use function ob_get_clean;
use function ob_start;
use function unlink;

/**
 * Test suite for the `ConfigCommand` class.
 *
 * This test case verifies the functionality of the ConfigCommand,
 * including the ability to generate and remove a cached configuration file
 * within a simulated application environment.
 *
 * The tests ensure that:
 * - A configuration cache file is correctly created by the `main()` method.
 * - The `clear()` method removes the configuration cache successfully.
 * - The environment is reset between tests to maintain isolation.
 *
 * Each test uses a mock application path under `assets/app1/` to simulate
 * real CLI behavior without affecting the actual system state.
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
#[CoversClass(ConfigCommand::class)]
class ConfigCommandTest extends TestCase
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
        if (file_exists($file = dirname(__DIR__, 2) . '/fixtures/console/app/bootstrap/cache/cache.php')) {
            @unlink($file);
        }
    }

    /**
     * Test it can create config file.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanCreateConfigFile(): void
    {
        $app = new Application(dirname(__DIR__, 2) . '/fixtures/console/app/');
        $app->setConfigPath('/config/');

        $command = new ConfigCommand([]);

        ob_start();
        $status = $command->main();
        $out    = ob_get_clean();

        $this->assertEquals(0, $status);
        $this->assertStringContainsString('Config file has successfully created.', $out);

        $app->flush();
    }

    /**
     * Test it can remove config file.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanRemoveConfigFile(): void
    {
        $app = new Application(dirname(__DIR__, 2) . '/fixtures/console/app/');
        $app->setConfigPath('/config/');

        $command = new ConfigCommand([]);

        ob_start();
        $command->main();
        $status = $command->clear();
        $out    = ob_get_clean();

        $this->assertEquals(0, $status);
        $this->assertStringContainsString('Config file has successfully created.', $out);

        $app->flush();
    }
}
