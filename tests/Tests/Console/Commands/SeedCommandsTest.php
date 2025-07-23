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

use Omega\Console\Commands\SeedCommand;
use PHPUnit\Framework\Attributes\CoversClass;

use function dirname;
use function file_exists;
use function file_get_contents;
use function ob_get_clean;
use function ob_start;
use function unlink;

/**
 * Unit test for the SeedCommand make feature.
 *
 * This class verifies the functionality of the `omega make:seed` console command,
 * including successful creation of seeder files, failure when required arguments are missing,
 * handling of already existing seeders, and forced overwriting of existing ones.
 *
 * Tests include validation of file creation, expected class structure, and error handling.
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
#[CoversClass(SeedCommand::class)]
class SeedCommandsTest extends CommandTestHelper
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
        parent::tearDown();

        $migration = dirname(__DIR__, 2) . '/fixtures/console/database/seeders/BaseSeeder.php';

        if (file_exists($migration)) {
            @unlink($migration);
        }
    }

    /**
     * Test it can call make command seeder with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandSeederWithSuccess(): void
    {
        $makeCommand = new SeedCommand($this->argv('omega make:seed BaseSeeder'));
        ob_start();
        $exit = $makeCommand->make();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = dirname(__DIR__, 2) . '/fixtures/console/database/seeders/BaseSeeder.php';
        $this->assertTrue(file_exists($file));

        $class = file_get_contents($file);
        $this->assertContain('class BaseSeeder extends Seeder', $class);
        $this->assertContain('public function run(): void', $class);
    }

    /**
     * Test it can call make command seed with fails.
     *
     * @return void
     */
    public function testItCanCallMakeCommandSeedWithFails(): void
    {
        $makeCommand = new SeedCommand($this->argv('omega make:seed'));
        ob_start();
        $exit = $makeCommand->make();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * Test it can call make command seed with fails file exist.
     *
     * @return void
     */
    public function testItCanCallMakeCommandSeedWithFailsFileExist(): void
    {
        app()->setSeederPath(dirname(__DIR__, 2) . '/fixtures/console/database/seeders/');
        $makeCommand = new SeedCommand($this->argv('omega make:seed BasicSeeder'));
        ob_start();
        $exit = $makeCommand->make();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * Test it can call make exist command seeder.
     *
     * @return void
     */
    public function testItCanCallMakeExistCommandSeeder(): void
    {
        app()->setSeederPath(dirname(__DIR__, 2) . '/fixtures/console/database/seeders/');
        $makeCommand = new SeedCommand($this->argv('omega make:seed ExistSeeder --force'));
        ob_start();
        $exit = $makeCommand->make();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = dirname(__DIR__, 2) . '/fixtures/console/database/seeders/ExistSeeder.php';
        $this->assertTrue(file_exists($file));

        $class = file_get_contents($file);
        $this->assertContain('class ExistSeeder extends Seeder', $class);
        $this->assertContain('public function run(): void', $class);
    }
}
