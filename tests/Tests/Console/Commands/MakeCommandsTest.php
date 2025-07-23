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

use Omega\Console\Commands\MakeCommand;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_map;
use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function glob;
use function ob_get_clean;
use function ob_start;
use function unlink;

/**
 * Test suite for the Omega Console MakeCommand class.
 *
 * This class verifies that all `omega make:*` CLI commands generate
 * the expected files (controllers, views, services, commands, migrations)
 * and fail appropriately when necessary.
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
class MakeCommandsTest extends CommandTestHelper
{
    /**
     * Set up the test environment before each test.
     *
     * Initializes the application with a custom Schedule instance
     * and binds it to the service container.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        if (!file_exists($command_config = dirname(__DIR__, 2) . '/fixtures/console/command.php')) {
            file_put_contents(
                $command_config,
                '<?php return array_merge(
                    // more command here
                );'
            );
        }
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
        parent::tearDownAfterClass();

        if (file_exists($command_config = dirname(__DIR__, 2) . '/fixtures/console/command.php')) {
            unlink($command_config);
        }

        if (file_exists($assetController = dirname(__DIR__, 2) . '/fixtures/console/IndexController.php')) {
            unlink($assetController);
        }

        if (file_exists($view = dirname(__DIR__, 2) . '/fixtures/console/welcome.template.php')) {
            unlink($view);
        }

        if (file_exists($service = dirname(__DIR__, 2) . '/fixtures/console/ApplicationService.php')) {
            unlink($service);
        }

        if (file_exists($command = dirname(__DIR__, 2) . '/fixtures/console/CacheCommand.php')) {
            unlink($command);
        }

        $migration = dirname(__DIR__, 2) . '/fixtures/console/migration/';
        array_map('unlink', glob("{$migration}/*.php"));
    }

    /**
     * Test it can call make command controller with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandControllerWithSuccess(): void
    {
        $makeCommand = new MakeCommand($this->argv('omega make:controller Index'));
        ob_start();
        $exit = $makeCommand->makeController();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = dirname(__DIR__, 2) . '/fixtures/console/IndexController.php';
        $this->assertTrue(file_exists($file));

        $class = file_get_contents($file);
        $this->assertContain('class IndexController extends Controller', $class);
        $this->assertContain('public function index(): Response', $class);
    }

    /**
     * Test it can call make command controller with fails.
     *
     * @return void
     */
    public function testItCanCallMakeCommandControllerWithFails(): void
    {
        $makeCommand = new MakeCommand($this->argv('omega make:controller Asset'));
        ob_start();
        $exit = $makeCommand->makeController();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * Test it can call make command view with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandViewWithSuccess(): void
    {
        $makeCommand = new MakeCommand($this->argv('omega make:view welcome'));
        ob_start();
        $exit = $makeCommand->makeView();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = dirname(__DIR__, 2) . '/fixtures/console/welcome.template.php';
        $this->assertTrue(file_exists($file));

        $view = file_get_contents($file);
        $this->assertContain('<title>Document</title>', $view);
    }

    /**
     * Test it can call make command view with fails.
     *
     * @return void
     */
    public function testItCanCallMakeCommandViewWithFails(): void
    {
        $makeCommand = new MakeCommand($this->argv('omega make:view asset'));
        ob_start();
        $exit = $makeCommand->makeView();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * Test it can call make command service with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandServiceWithSuccess(): void
    {
        $make_service = new MakeCommand($this->argv('omega make:service Application'));
        ob_start();
        $exit = $make_service->makeServices();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = dirname(__DIR__, 2) . '/fixtures/console/ApplicationService.php';
        $this->assertTrue(file_exists($file));

        $service = file_get_contents($file);
        $this->assertContain('class ApplicationService extends Service', $service);
    }

    /**
     * Test it can call make command service with fails.
     *
     * @return void
     */
    public function testItCanCallMakeCommandServiceWithFails(): void
    {
        $make_service = new MakeCommand($this->argv('omega make:service Asset'));
        ob_start();
        $exit = $make_service->makeServices();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * Test it can call make command a commands with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandACommandsWithSuccess(): void
    {
        $make_command = new MakeCommand($this->argv('omega make:command Cache'));
        ob_start();
        $exit = $make_command->makeCommand();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = dirname(__DIR__, 2) . '/fixtures/console/CacheCommand.php';
        $this->assertTrue(file_exists($file));

        $command = file_get_contents($file);
        $this->assertContain('class CacheCommand extends Command', $command);
    }

    /**
     * Test it can call make command a commands with fails.
     *
     * @return void
     */
    public function testItCanCallMakeCommandACommandsWithFails(): void
    {
        $make_command = new MakeCommand($this->argv('omega make:command Asset'));
        ob_start();
        $exit = $make_command->makeCommand();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * Test it can call make command migration with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandMigrationWithSuccess(): void
    {
        $make_command = new MakeCommand($this->argv('omega make:migration user'));
        ob_start();
        $exit = $make_command->makeMigration();
        ob_get_clean();

        $this->assertSuccess($exit);

        $make_command = new MakeCommand($this->argv('omega make:migration guest --update'));
        ob_start();
        $exit = $make_command->makeMigration();
        ob_get_clean();

        $this->assertSuccess($exit);
    }
}
