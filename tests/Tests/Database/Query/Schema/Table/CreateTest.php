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

namespace Tests\Database\Query\Schema\Table;

use Exception;
use Omega\Database\Schema\Table\Column;
use Omega\Database\Schema\Table\Create;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabaseQuery;

/**
 * Test suite for the Create and Column schema classes.
 *
 * This class verifies the generation of SQL CREATE TABLE statements using
 * various combinations of columns, constraints, and table options such as
 * primary keys, unique indexes, default values, storage engines, and character sets.
 *
 * The tests cover both raw column definitions and fluent column declarations,
 * ensuring that the resulting SQL matches expected MySQL syntax for a wide
 * range of use cases.
 *
 * @category   Omega\Tests
 * @package    Database
 * @subpackage Schema\Table
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Column::class)]
#[CoversClass(Create::class)]
class CreateTest extends AbstractDatabaseQuery
{
    /**
     * Test it can generate query using add column.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingAddColumn(): void
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schema);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');
        $schema->primaryKey('PersonID');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (PersonID) )',
            $schema->__toString()
        );
    }

    /**
     * Test it can generate query using with multi primary key.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingWithMultiPrimaryKey(): void
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schema);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');
        $schema->primaryKey('PersonID');
        $schema->primaryKey('LastName');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (PersonID, LastName) )',
            $schema->__toString()
        );
    }

    /**
     * Test it can generate query using add colum without primary key.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingAddColumnWithoutPrimaryKey(): void
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schema);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255) )',
            $schema->__toString()
        );
    }

    /**
     * Test it can generate query using add column with unique.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingAddColumnWithUnique(): void
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schema);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');
        $schema->unique('PersonID');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), UNIQUE (PersonID) )',
            $schema->__toString()
        );
    }

    /**
     * test it can generate query using add column with multi unique.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingAddColumnWithMultiUnique(): void
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schema);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');
        $schema->unique('PersonID');
        $schema->unique('LastName');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), UNIQUE (PersonID, LastName) )',
            $schema->__toString()
        );
    }

    /**
     * Test it can generate query using columns.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingColumns(): void
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schema);
        $schema->columns([
            (new Column())->raw('PersonID int'),
            (new Column())->raw('LastName varchar(255)'),
        ]);
        $schema->primaryKey('PersonID');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (PersonID) )',
            $schema->__toString()
        );
    }

    /**
     * Test it can generate query.
     *
     * @return void
     */
    public function testItCanGenerateQuery(): void
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schema);
        $schema('PersonID')->int();
        $schema('LastName')->varchar(255);
        $schema->primaryKey('PersonID');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (PersonID) )',
            $schema->__toString()
        );
    }

    /**
     * Test it can generate default constraint.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGenerateDefaultConstraint(): void
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schema);
        $schema('PersonID')->int()->unsigned()->default(1);
        $schema('LastName')->varchar(255)->default('-');
        $schema('suffix')->varchar(15)->defaultNull();
        $schema->primaryKey('PersonID');

        $this->assertEquals(
            "CREATE TABLE testing_db.test ( PersonID int UNSIGNED DEFAULT 1, LastName varchar(255) DEFAULT '-', suffix varchar(15) DEFAULT NULL, PRIMARY KEY (PersonID) )",
            $schema->__toString()
        );
    }

    /**
     * Test it can generate query with data type and constraint.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGenerateQueryWithDatatypeAndConstraint(): void
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schema);
        $schema('PersonID')->int()->notNull();
        $schema('LastName')->varchar(255)->null();
        $schema->primaryKey('PersonID');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int NOT NULL, LastName varchar(255) NULL, PRIMARY KEY (PersonID) )',
            $schema->__toString()
        );
    }

    /**
     * Test it can generate query with storage engine.
     *
     * @return void
     */
    public function testItCanGenerateQueryWithStorageEngine(): void
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schema);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');
        $schema->primaryKey('PersonID');
        $schema->engine(Create::INNODB);

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (PersonID) ) ENGINE=INNODB',
            $schema->__toString()
        );
    }

    /**
     * Test it can generate query with character set.
     *
     * @return void
     */
    public function testItCanGenerateQueryWithCharacterSet(): void
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schema);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');
        $schema->primaryKey('PersonID');
        $schema->character('utf8mb4');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (PersonID) ) CHARACTER SET utf8mb4',
            $schema->__toString()
        );
    }

    /**
     * Test it can generate query with engine store and character set.
     *
     * @return void
     */
    public function testItCanGenerateQueryWithEngineStoreAndCharacterSet(): void
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schema);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');
        $schema->primaryKey('PersonID');
        $schema->engine(Create::INNODB);
        $schema->character('utf8mb4');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (PersonID) ) ENGINE=INNODB CHARACTER SET utf8mb4',
            $schema->__toString()
        );
    }
}
