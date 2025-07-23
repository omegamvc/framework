<?php

/**
 * Part of Omega - Tests\Database Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Database;

use Omega\Database\Schema\Schema;
use Omega\Database\Schema\Table\Alter;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Unit tests for the Schema class and its related behavior using Alter.
 *
 * This test case verifies schema modification capabilities such as altering
 * and executing raw queries on the database tables.
 *
 * @category  Omega\Tests
 * @package   Database
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Alter::class)]
#[CoversClass(Schema::class)]
class SchemaTest extends AbstractDatabase
{
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
        $this->createConnection();
        $this->createUserSchema();
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
    }

    /**
     * Test it can update database table.
     *
     * @return void
     */
    public function testItCanUpdateDatabaseTable(): void
    {
        $schema = new Schema($this->pdo_schema);

        $alter = $schema->alter('users', function (Alter $blueprint) {
            $blueprint->column('user')->varchar(20);
            $blueprint->drop('stat');
            $blueprint->add('status')->int(3);
        });

        $this->assertTrue($alter->execute());
    }

    /**
     * Test it can execute using raw query.
     *
     * @return void
     */
    public function testItCanExecuteUsingRawQuery(): void
    {
        $schema = new Schema($this->pdo_schema);
        $raw    = $schema->raw(
            'ALTER TABLE testing_db.users MODIFY COLUMN user varchar(20), ADD status int(3), DROP COLUMN stat'
        );

        $this->assertTrue($raw->execute());
    }
}
