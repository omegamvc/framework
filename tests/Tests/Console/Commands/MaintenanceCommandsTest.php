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

use Omega\Console\Commands\MaintenanceCommand;
use PHPUnit\Framework\Attributes\CoversClass;

use function file_exists;
use function filemtime;
use function ob_get_clean;
use function ob_start;
use function unlink;

/**
 * Test class for validating maintenance mode command behaviors.
 *
 * This class covers various scenarios for enabling and disabling
 * maintenance mode using the MaintenanceCommand, including:
 * - Creating the necessary `down` and `maintenance.php` files when entering maintenance mode.
 * - Ensuring a fresh configuration file is generated on repeated down commands.
 * - Handling failure cases when attempting to re-enter or exit maintenance mode improperly.
 * - Cleaning up temporary maintenance-related files after each test.
 *
 * Extends the base CommandTestHelper class for utility assertions and setup.
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
#[CoversClass(MaintenanceCommand::class)]
class MaintenanceCommandsTest extends CommandTestHelper
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
        if (file_exists($down = storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down')) {
            unlink($down);
        }

        if (file_exists($maintenance = storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php')) {
            unlink($maintenance);
        }

        parent::tearDown();
    }

    /**
     * Test it can make down maintenance mode.
     *
     * @return void
     */
    public function testItCanMakeDownMaintenanceMode(): void
    {
        $down = new MaintenanceCommand(['down']);

        $this->assertFileDoesNotExist(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down');
        $this->assertFileDoesNotExist(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');

        ob_start();
        $this->assertSuccess($down->down());
        ob_get_clean();

        $this->assertFileExists(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down');
        $this->assertFileExists(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');
    }

    /**
     * Test it can make down maintenance mode fresh down config.
     *
     * @return void
     */
    public function testItCanMakeDownMaintenanceModeFreshDownConfig(): void
    {
        $command = new MaintenanceCommand(['command']);
        ob_start();
        $command->down();

        $start = 0;

        if (file_exists($down = storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down')) {
            $start = filemtime($down);
        }

        $command->down();
        $end = filemtime($down);
        ob_get_clean();

        $this->assertGreaterThanOrEqual($end, $start);
        $this->assertFileExists(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down');
        $this->assertFileExists(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');
    }

    /**
     * Test it can make down maintenance mode fail.
     *
     * @return void
     */
    public function testItCanMakeDownMaintenanceModeFail(): void
    {
        $down = new MaintenanceCommand(['down']);

        ob_start();
        $this->assertSuccess($down->down());
        $this->assertFails($down->down());
        ob_get_clean();
    }

    /**
     * Test it can make up maintenance mode.
     *
     * @return void
     */
    public function testItCanMakeUpMaintenanceMode(): void
    {
        $command = new MaintenanceCommand(['up']);

        ob_start();
        $command->down();

        $this->assertFileExists(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down');
        $this->assertFileExists(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');
        $this->assertSuccess($command->up());

        ob_get_clean();
    }

    /**
     * Test it can make up maintenance mode but fail.
     *
     * @return void
     */
    public function testItCanMakeUpMaintenanceModeButFail(): void
    {
        $command = new MaintenanceCommand(['up']);

        ob_start();
        $this->assertFails($command->up());
        ob_get_clean();
    }
}
