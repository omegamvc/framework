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

use Omega\Console\Commands\MigrationCommand;
use Omega\Database\Connection;
use Omega\Database\Schema\Table\Create;
use Omega\Application\Application;
use Omega\Support\Facades\Facade;
use Omega\Support\Facades\Schema;
use Omega\Text\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabase;

use function ob_get_clean;
use function ob_start;

require_once dirname(__DIR__, 2) . '/Database/AbstractDatabase.php';
/**
 * Test suite for the MigrationCommand class and related database migration features.
 *
 * This test covers various migration-related commands such as migrate, fresh, reset,
 * refresh, rollback, and database operations including create, show, drop, and init.
 * It ensures that migration flows work correctly within the application context, using
 * an in-memory test database.
 *
 * Note: This test case must be run as part of the full test suite using `vendor/bin/phpunit`,
 * as it depends on preloaded fixtures and environment setup.
 * Also covers the core routing behavior through the Router class.
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
#[CoversClass(Create::class)]
#[CoversClass(Facade::class)]
#[CoversClass(MigrationCommand::class)]
#[CoversClass(Connection::class)]
#[CoversClass(Schema::class)]
#[CoversClass(Str::class)]
class MigrationCommandsTest extends AbstractDatabase
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

        $this->app = new Application(dirname(__DIR__, 2));
        $this->app->setMigrationPath('/fixtures/console/database/migration/');
        $this->app->set('environment', 'dev');
        $this->app->set(Connection::class, fn () => $this->pdo);
        $this->app->set('Schema', fn () => $this->schema);
        $this->app->set('dsn.sql', fn () => $this->env);

        Facade::setFacadeBase($this->app);
        Schema::table('migration', function (Create $column) {
            $column('migration')->varchar(100)->notNull();
            $column('batch')->int(4)->notNull();

            $column->unique('migration');
        })->execute();
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
        Schema::drop()->table('migration')->ifExists()->execute();
        MigrationCommand::flushVendorMigrationPaths();
        $this->app->flush();
    }

    /**
     * Test it can run migration return success and success migrate
     *
     * @return void
     */
    public function testItCanRunMigrationReturnSuccessAndSuccessMigrate(): void
    {
        $migrate = new MigrationCommand(['omega', 'migrate']);
        ob_start();
        $exit = $migrate->main();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * Test it can run migration fresh return success and success migrate.
     *
     * @return void
     */
    public function testItCanRunMigrationFreshReturnSuccessAndSuccessMigrate(): void
    {
        $migrate = new MigrationCommand(['omega', 'migrate:fresh']);
        ob_start();
        $exit = $migrate->fresh(true);
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, 'success drop database `testing_db`'));
        $this->assertTrue(Str::contains($out, 'success create database `testing_db`'));
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * Test it can run migration reset return success and success migrate,
     *
     * @return void
     */
    public function testItCanRunMigrationResetReturnSuccessAndSuccessMigrate(): void
    {
        $migrate = new MigrationCommand(['omega', 'migrate:reset']);
        ob_start();
        $exit = $migrate->reset();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * Test it can run migration refresh return success and success migrate.
     *
     * @rreturn void
     */
    public function testItCanRunMigrationRefreshReturnSuccessAndSuccessMigrate(): void
    {
        $migrate = new MigrationCommand(['omega', 'migrate:refresh']);
        ob_start();
        $exit = $migrate->refresh();
        $out  = ob_get_clean();


        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * Test it can run migration rollback return success and success migrate.
     *
     * @return void
     */
    public function testItCanRunMigrationRollbackReturnSuccessAndSuccessMigrate(): void
    {
        $migrate = new MigrationCommand(['omega', 'migrate:rollback', '--batch=0']);
        ob_start();
        $exit = $migrate->rollback();
        $out  = ob_get_clean();


        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * Test it can run database create.
     *
     * @return void
     */
    public function testItCanRunDatabaseCreate(): void
    {
        $migrate = new MigrationCommand(['omega', 'db:create']);
        ob_start();
        $exit = $migrate->databaseCreate(true);
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, 'success create database `testing_db`'));
    }

    /**
     * Test it can run database show.
     *
     * @return void
     */
    public function testItCanRunDatabaseShow(): void
    {
        $migrate = new MigrationCommand(['omega', 'db:show']);
        ob_start();
        $exit = $migrate->databaseShow();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, 'migration'));
    }

    /**
     * Test it can run database drop.
     *
     * @return void
     */
    public function testItCanRunDatabaseDrop(): void
    {
        $migrate = new MigrationCommand(['omega', 'db:drop']);
        ob_start();
        $exit = $migrate->databaseDrop(true);
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, 'success drop database `testing_db`'));
    }

    /**
     * Test it can run migration init.
     *
     * @retunr void
     */
    public function testItCanRunMigrateInit(): void
    {
        $migrate = new MigrationCommand(['omega', 'migrate:init']);
        ob_start();
        $exit    = $migrate->initializeMigration();
        $out     = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, 'Migration table already exist on your database table.'));
    }

    /**
     * Test it can pass confirmation using option yes.
     *
     * @return void
     */
    public function testItCanPassConfirmationUsingOptionYes(): void
    {
        $confirmation = (fn () => $this->{'confirmation'}('message?'))->call(new MigrationCommand(['omega', 'db:create'], ['yes' => true]));
        $this->assertTrue($confirmation);
    }

    /**
     * Test it can run migration from vendor.
     *
     * @return void
     */
    public function testItCanRunMigrationFromVendor(): void
    {
        $migrate = new MigrationCommand(['omega', 'migrate']);
        MigrationCommand::addVendorMigrationPath(dirname(__DIR__, 2) . '/fixtures/console/database/vendor-migration/');
        ob_start();
        $exit = $migrate->main();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, '2024_06_12_070600_clients'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }
}
