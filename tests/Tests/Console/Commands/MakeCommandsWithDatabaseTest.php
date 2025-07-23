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
use Omega\Database\Connection;
use Omega\Database\Query\Query;
use Omega\Application\Application;
use Omega\Support\Facades\PDO;
use Omega\Support\Facades\Schema;
use Omega\Text\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabase;

use function dirname;
use function file_exists;
use function file_get_contents;
use function ob_get_clean;
use function ob_start;
use function unlink;

/**
 * Test suite for the `make:model` command with active database integration.
 *
 * This class ensures that model generation works correctly when the command
 * has access to a live database connection. It verifies that metadata such as
 * table name and primary key are correctly injected into the generated model.
 *
 * It sets up an in-memory environment using `Application`, `Connection`, and `Schema`,
 * and cleans up the generated files after each run.
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
#[CoversClass(MakeCommand::class)]
#[CoversClass(Query::class)]
#[CoversClass(Connection::class)]
#[CoversClass(Schema::class)]
#[CoversClass(Str::class)]
class MakeCommandsWithDatabaseTest extends AbstractDatabase
{
    /** @var Application Holds the current application instance  */
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

        $this->app = new Application('');
        $this->app->set('environment', 'dev');
        new Schema($this->app);
        new PDO($this->app);
        $this->app->set(Connection::class, $this->pdo);
        $this->app->set('Schema', $this->schema);
        $this->app->set('dsn.sql', $this->env);
        $this->app->set('Query', fn () => new Query($this->pdo));
        $this->app->setModelPath(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR);
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

        if (file_exists($client =  dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR . 'Client.php')) {
            unlink($client);
        }
    }

    /**
     * Test it can call make command model with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandModelWithSuccess(): void
    {
        $make_model = new MakeCommand(['omega', 'make:model', 'Client', '--table-name', 'users']);
        ob_start();
        $exit = $make_model->makeModel();
        ob_get_clean();

        $this->assertEquals(0, $exit);

        $file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR . 'Client.php';
        $this->assertTrue(file_exists($file));

        $model = file_get_contents($file);
        $this->assertTrue(Str::contains($model, 'protected string $' . "table_name  = 'users'"));
        $this->assertTrue(Str::contains($model, 'protected string $' . "primary_key = 'id'"));
    }
}
