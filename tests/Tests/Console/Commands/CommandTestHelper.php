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

use Omega\Application\Application;
use Omega\Text\Str;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function explode;

/**
 * Test case for command-related functionality within the application.
 *
 * This class sets up a controlled application environment for
 * testing various commands, managing paths to resources such as views,
 * controllers, services, models, commands, configs, migrations, seeders,
 * and storage.
 *
 * It also provides helper methods to simplify command execution
 * assertions and argument parsing during tests.
 *
 * @internal
 * @category   Omega\Tests
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversNothing]
class CommandTestHelper extends TestCase
{
    /**
     * Instance of the Application under test.
     *
     * This application object is initialized before each test and
     * cleaned up after each test to maintain isolation.
     *
     * @var Application|null
     */
    protected ?Application $app;

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
        $this->app = new Application('');

        $this->app->setViewPath(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR);
        $this->app->setControllerPath(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR);
        $this->app->setServicesPath(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR);
        $this->app->setModelPath(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR);
        $this->app->setCommandPath(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR);
        $this->app->setConfigPath(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR);

        $this->app->setMigrationPath(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR);
        $this->app->setSeederPath(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR);
        $this->app->setStoragePath(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR);
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
     * Helper to split a command line string into an argument array.
     *
     * @param string $argv Command line string to split
     * @return string[] Parsed array of arguments
     */
    protected function argv(string $argv): array
    {
        return explode(' ', $argv);
    }

    /**
     * Assert that a command exited with a success code (0).
     *
     * @param int $code Exit code returned by the command
     * @return void
     */
    protected function assertSuccess(int $code): void
    {
        Assert::assertEquals(0, $code, 'Command exit with success code');
    }

    /**
     * Assert that a command exited with a failure code (> 0).
     *
     * @param int $code Exit code returned by the command
     * @return void
     */
    protected function assertFails(int $code): void
    {
        Assert::assertGreaterThan(0, $code, 'Command exit with fail code');
    }

    /**
     * Assert that a given string contains a specified substring.
     *
     * @param string $contain The substring expected to be found
     * @param string $in The full string to search within
     * @return void
     */
    public function assertContain(string $contain, string $in): void
    {
        Assert::assertTrue(Str::contains($in, $contain), "This $contain is contain in $in.");
    }
}
