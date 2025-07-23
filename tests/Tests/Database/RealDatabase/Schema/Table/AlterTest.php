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

use Omega\Database\Schema\Table\Alter;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabase;

/**
 * Unit tests for the Alter class, which is responsible for generating
 * SQL ALTER TABLE statements to modify an existing table structure.
 *
 * This test suite covers all available alterations including modifying,
 * adding, dropping, and renaming columns, as well as column ordering.
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
#[CoversClass(Alter::class)]
class AlterTest extends AbstractDatabase
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
        $this->pdo
            ->query('CREATE TABLE profiles (
                user varchar(10) NOT NULL,
                name varchar(500) NOT NULL,
                stat int(2) NOT NULL,
                create_at int(12) NOT NULL,
                update_at int(12) NOT NULL,
                PRIMARY KEY (user)
              )')
            ->execute();
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
     * Test it can execute query using modify column,
     *
     * @return void
     */
    public function testItCanExecuteQueryUsingModifyColumn(): void
    {
        $schema = new Alter(
            $this->pdo_schema->getConfig()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema->column('user')->varchar(15);

        $this->assertTrue($schema->execute());
    }

    /**
     * Test it can execute query using add column.
     *
     * @return void
     */
    public function testItCanExecuteQueryUsingAddColumn(): void
    {
        $schema = new Alter(
            $this->pdo_schema->getConfig()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema->add('PersonID')->int();
        $schema->add('LastName')->varchar(255);

        $this->assertTrue($schema->execute());
    }

    /**
     * Test it can execute query using drop column.
     *
     * @return void
     */
    public function testItCanExecuteQueryUsingDropColumn(): void
    {
        $schema = new Alter(
            $this->pdo_schema->getConfig()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema->drop('create_at');
        $schema->drop('update_at');

        $this->assertTrue($schema->execute());
    }

    /**
     * Test it can execute query using rename column.
     *
     * @return void
     */
    public function testItCanExecuteQueryUsingRenameColumn(): void
    {
        $schema = new Alter(
            $this->pdo_schema->getConfig()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema->rename('stat', 'take');

        $this->assertTrue($schema->execute());
    }

    /**
     * Test it can execute query using rename column.
     *
     * @return void
     */
    public function testItCanExecuteQueryUsingRenamesColumn(): void
    {
        $schema = new Alter(
            $this->pdo_schema->getConfig()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema->rename('stat', 'take');
        $schema->rename('update_at', 'modify_at');

        $this->assertTrue($schema->execute());
    }

    /**
     * Test it can execute query using alter column.
     *
     * @return void
     */
    public function testItCanExecuteQueryUsingAlterColumn(): void
    {
        $schema = new Alter(
            $this->pdo_schema->getConfig()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema->column('user')->varchar(15);
        $schema->add('PersonID')->int();
        $schema->drop('create_at');
        $schema->rename('stat', 'take');

        $this->assertTrue($schema->execute());
    }

    /**
     * Test it can execute query using modify add wth order.
     *
     * @return void
     */
    public function testItCanExecuteQueryUsingModifyAddWithOrder(): void
    {
        $schema = new Alter(
            $this->pdo_schema->getConfig()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema->add('uuid')->varchar(15)->first();
        $schema->add('last_name')->varchar(32)->after('name');

        $this->assertTrue($schema->execute());
    }

    /**
     * Test it can execute query using modify column with order.
     *
     * @return void
     */
    public function testItCanExecuteQueryUsingModifyColumnWithOrder(): void
    {
        $schema = new Alter(
            $this->pdo_schema->getConfig()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema('create_at')->varchar(15)->after('user');
        $schema->column('update_at')->varchar(15)->after('user');

        $this->assertTrue($schema->execute());
    }
}
