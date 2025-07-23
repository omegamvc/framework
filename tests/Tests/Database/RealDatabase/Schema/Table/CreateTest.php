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

namespace Tests\Database\RealDatabase\Schema\Table;

use Exception;
use Omega\Database\Schema\Table\Create;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabase;

/**
 * Unit tests for the Create class, which is responsible for generating
 * and executing CREATE TABLE queries with various SQL constraints.
 *
 * These tests cover creation of tables with:
 * - Primary keys (single and multiple)
 * - Unique constraints
 * - Default values
 * - Custom storage engine and character set
 *
 * @category   Omega\Tests
 * @package    Database
 * @subpackage RealDatabase\Schema\Table
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Create::class)]
class CreateTest extends AbstractDatabase
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
     * Test it can create database.
     *
     * @return void
     */
    public function testItCanCreateDatabase(): void
    {
        $schema = new Create($this->pdo_schema->getConfig()['database_name'], 'profiles', $this->pdo_schema);

        $schema('id')->int(3)->notNull();
        $schema('name')->varchar(32)->notNull();
        $schema('gender')->int(1);
        $schema->primaryKey('id');

        $this->assertTrue($schema->execute());
    }

    /**
     * Test it can execute a query with multiple primary keys.
     *
     * @return void
     */
    public function testItCanExecuteQueryWithMultiplePrimaryKeys(): void
    {
        $schema = new Create($this->pdo_schema->getConfig()['database_name'], 'profiles', $this->pdo_schema);

        $schema('id')->int(3)->notNull();
        $schema('xid')->int(3)->notNull();
        $schema('name')->varchar(32)->notNull();
        $schema('gender')->int(1);
        $schema->primaryKey('id');
        $schema->primaryKey('xid');

        $this->assertTrue($schema->execute());
    }

    /**
     * Test it can execute a query with multiple unique constraints.
     *
     * @return void
     */
    public function testItCanExecuteQueryWithMultipleUniqueConstraints(): void
    {
        $schema = new Create($this->pdo_schema->getConfig()['database_name'], 'profiles', $this->pdo_schema);

        $schema('id')->int(3)->notNull();
        $schema('name')->varchar(32)->notNull();
        $schema('gender')->int(1);
        $schema->unique('id');
        $schema->unique('name');

        $this->assertTrue($schema->execute());
    }

    /**
     * Test it can create database with engine.
     *
     * @return void
     */
    public function testItCanCreateDatabaseWithEngine(): void
    {
        $schema = new Create($this->pdo_schema->getConfig()['database_name'], 'profiles', $this->pdo_schema);

        $schema('id')->int(3)->notNull();
        $schema('name')->varchar(32)->notNull();
        $schema('gender')->int(1);
        $schema->primaryKey('id');
        $schema->engine(Create::INNODB);
        $schema->character('utf8mb4');

        $this->assertTrue($schema->execute());
    }

    /**
     * Test it can generate default constraints.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGenerateDefaultConstraint(): void
    {
        $schema = new Create($this->pdo_schema->getConfig()['database_name'], 'profiles', $this->pdo_schema);
        $schema('PersonID')->int()->unsigned()->default(1);
        $schema('LastName')->varchar(255)->default('-');
        $schema('suffix')->varchar(15)->defaultNull();
        $schema->primaryKey('PersonID');

        $this->assertTrue($schema->execute());
    }
}
