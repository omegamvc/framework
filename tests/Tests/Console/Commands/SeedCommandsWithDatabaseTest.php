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
use Omega\Database\Connection;
use Omega\Application\Application;
use Omega\Support\Facades\DB;
use Omega\Support\Facades\PDO as FacadesPDO;
use Omega\Support\Facades\Schema;
use Omega\Text\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabase;

use function dirname;
use function ob_get_clean;
use function ob_start;

/**
 * Unit tests for the database seeder command.
 *
 * This class tests the SeedCommand functionality within the Omega framework,
 * including the execution of different seeder classes, handling of namespace overrides,
 * chaining of seeders, and real data insertion into the database.
 * It relies on a temporary SQLite database initialized via AbstractDatabase.
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
#[CoversClass(Application::class)]
#[CoversClass(DB::class)]
#[CoversClass(Connection::class)]
#[CoversClass(FacadesPDO::class)]
#[CoversClass(Schema::class)]
#[CoversClass(SeedCommand::class)]
#[CoversClass(Str::class)]
class SeedCommandsWithDatabaseTest extends AbstractDatabase
{
    /** @var Application Holds the current application instance. */
    private Application $app;

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
        $this->createConnection();
        $this->createUserSchema();

        require_once dirname(__DIR__, 2) . '/fixtures/console/database/seeders/BasicSeeder.php';
        require_once dirname(__DIR__, 2) . '/fixtures/console/database/seeders/UserSeeder.php';
        require_once dirname(__DIR__, 2) . '/fixtures/console/database/seeders/ChainSeeder.php';
        require_once dirname(__DIR__, 2) . '/fixtures/console/database/seeders/CustomNamespaceSeeder.php';
        $this->app = new Application(dirname(__DIR__, 2));
        $this->app->setSeederPath('/fixtures/console/database/seeders/');
        $this->app->set('environment', 'dev');
        new Schema($this->app);
        new FacadesPDO($this->app);
        new DB($this->app);
        $this->app->set(Connection::class, $this->pdo);
        $this->app->set('Schema', $this->schema);
        $this->app->set('dsn.sql', $this->env);
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
        $this->dropConnection();
        $this->app->flush();
    }

    /**
     * Test it can run seeder.
     *
     * @return void
     */
    public function testItCanRunSeeder(): void
    {

        $seeder = new SeedCommand(['omega', 'db:seed', '--class', 'BasicSeeder']);
        ob_start();
        $seeder->main();
        $out  = ob_get_clean();

        $this->assertTrue(Str::contains($out, 'seed for basic seeder'));
        $this->assertTrue(Str::contains($out, 'Success run seeder'));
    }

    /**
     * Test it can run seeder runner with real insert data.
     *
     * @return void
     */
    public function testItCanRunSeederRunnerWithRealInsertData(): void
    {
        $seeder = new SeedCommand(['omega', 'db:seed', '--class', 'UserSeeder']);
        ob_start();
        $seeder->main();
        $out  = ob_get_clean();

        $this->assertTrue(Str::contains($out, 'Success run seeder'));
    }

    /**
     * Test it can run seeder with custom namespace.
     *
     * @return void
     */
    public function testItCanRunSeederWithCustomNamespace(): void
    {
        $seeder = new SeedCommand(['omega', 'db:seed', '--name-space', 'CustomNamespaceSeeder']);
        ob_start();
        $seeder->main();
        $out  = ob_get_clean();

        $this->assertTrue(Str::contains($out, 'Success run seeder'));
    }

    /**
     * Test it can run seeder with call other.
     *
     * @return void
     */
    public function testItCanRunSeederWithCallOther(): void
    {
        $seeder = new SeedCommand(['omega', 'db:seed', '--class', 'ChainSeeder']);
        ob_start();
        $seeder->main();
        $out  = ob_get_clean();

        $this->assertTrue(Str::contains($out, 'seed for basic seeder'));
        $this->assertTrue(Str::contains($out, 'Success run seeder'));
    }
}
