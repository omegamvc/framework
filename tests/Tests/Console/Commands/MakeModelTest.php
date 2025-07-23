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

use Omega\Console\Commands\MakeCommand;
use PHPUnit\Framework\Attributes\CoversClass;

use function dirname;
use function file_exists;
use function file_get_contents;
use function ob_get_clean;
use function ob_start;
use function unlink;

/**
 * Test suite for the `make:model` console command.
 *
 * This class tests the behavior of the `make:model` command,
 * ensuring that it generates the correct model files with various
 * options, including table name customization and forced overwriting.
 *
 * It verifies file creation, content structure, and command success/failure states.
 * The generated files are stored in the fixtures directory and cleaned up after each test.
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
#[CoversClass(MakeCommand::class)]
class MakeModelTest extends CommandTestHelper
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

        if (file_exists($model = dirname(__DIR__, 2) . '/fixtures/console/User2.php')) {
            unlink($model);
        }
    }

    /**
     * Test it can call make command model with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandModelWithSuccess(): void
    {
        $make_model = new MakeCommand($this->argv('omega make:model User2'));
        ob_start();
        $exit = $make_model->makeModel();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = dirname(__DIR__, 2) . '/fixtures/console/User2.php';
        $this->assertTrue(file_exists($file));

        $model = file_get_contents($file);
        $this->assertContain('class User2 extends Model', $model);
    }

    /**
     * Test it can call make command model with exist model.
     *
     * @return void
     */
    public function testItCanCallMakeCommandModelWithExistModel(): void
    {
        $make_model = new MakeCommand($this->argv('omega make:model User --table-name=users --force'));
        ob_start();
        $exit = $make_model->makeModel();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = dirname(__DIR__, 2) . '/fixtures/console/User.php';
        $this->assertTrue(file_exists($file));

        $model = file_get_contents($file);
        $this->assertContain('class User extends Model', $model);
    }

    /**
     * est it can call make command model with table name and return success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandModelWithTableNameAndReturnSuccess(): void
    {
        $make_model = new MakeCommand($this->argv('omega make:model User2 --table-name users'));
        ob_start();
        $exit = $make_model->makeModel();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = dirname(__DIR__, 2) . '/fixtures/console/User2.php';
        $this->assertTrue(file_exists($file));

        $model = file_get_contents($file);
        $this->assertContain('class User2 extends Model', $model);
    }

    /**
     * Test it can call make command model return fails.
     *
     * @return void
     */
    public function testItCanCallMakeCommandModelReturnFails(): void
    {
        $make_model = new MakeCommand($this->argv('omega make:model Asset'));
        ob_start();
        $exit = $make_model->makeModel();
        ob_get_clean();

        $this->assertFails($exit);
    }
}
