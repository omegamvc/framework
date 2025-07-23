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

use Omega\Database\Schema\Table\Alter;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabaseQuery;

/**
 * Test suite for the Alter schema class.
 *
 * This class verifies the behavior of the Alter class used to generate
 * SQL ALTER TABLE statements. It covers all major operations including
 * adding, dropping, modifying, and renaming columns, as well as column ordering.
 *
 * Each test ensures that the generated SQL matches the expected syntax,
 * validating both individual and combined alteration scenarios.
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
#[CoversClass(Alter::class)]
class AlterTest extends AbstractDatabaseQuery
{
    /**
     * test it can generate query using modify column.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingModifyColumn(): void
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schema);
        $schema->column('create_add')->int(17);
        $schema('update_add')->int(17);

        $this->assertEquals(
            'ALTER TABLE testing_db.test MODIFY COLUMN create_add int(17), MODIFY COLUMN update_add int(17);',
            $schema->__toString()
        );
    }

    /**
     * Test it can generate query using add column.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingAddColumn(): void
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schema);
        $schema->add('PersonID')->int();
        $schema->add('LastName')->varchar(255);

        $this->assertEquals(
            'ALTER TABLE testing_db.test ADD PersonID int, ADD LastName varchar(255);',
            $schema->__toString()
        );
    }

    /**
     * Test it can generate query using drop column.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingDropColumn(): void
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schema);
        $schema->drop('PersonID');
        $schema->drop('LastName');

        $this->assertEquals(
            'ALTER TABLE testing_db.test DROP COLUMN PersonID, DROP COLUMN LastName;',
            $schema->__toString()
        );
    }

    /**
     * Test it can generate query using rename column.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingRenameColumn(): void
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schema);
        $schema->rename('PersonID', 'person_id');

        $this->assertEquals(
            'ALTER TABLE testing_db.test RENAME COLUMN PersonID TO person_id;',
            $schema->__toString()
        );
    }

    /**
     * Test it can generate query using rename column multiple.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingRenameColumnMultiple(): void
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schema);
        $schema->rename('PersonID', 'person');
        $schema->rename('PersonID', 'person_id');

        $this->assertEquals(
            'ALTER TABLE testing_db.test RENAME COLUMN PersonID TO person_id;',
            $schema->__toString(),
            'multi rename column will use last rename'
        );
    }

    /**
     * Test it can generate query using alters column.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingAltersColumn(): void
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schema);
        $schema->add('PersonID')->int(4);
        $schema->drop('LastName');
        $schema->column('create_add')->int(17);

        $this->assertEquals(
            'ALTER TABLE testing_db.test MODIFY COLUMN create_add int(17), ADD PersonID int(4), DROP COLUMN LastName;',
            $schema->__toString()
        );
    }

    /**
     * Test it can generate query using modify column and order it.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingModifyColumnAndOrderIt(): void
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schema);
        $schema->column('uuid')->int(17)->first();
        $schema->column('create_add')->after('id');

        $this->assertEquals(
            'ALTER TABLE testing_db.test MODIFY COLUMN uuid int(17) FIRST, MODIFY COLUMN create_add AFTER id;',
            $schema->__toString()
        );
    }

    /**
     * Test it can generate query using add column and order it.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingAddColumnAndOrderIt(): void
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schema);
        $schema->add('uuid')->int(17)->first();
        $schema->add('create_add')->int(17)->after('id');

        $this->assertEquals(
            'ALTER TABLE testing_db.test ADD uuid int(17) FIRST, ADD create_add int(17) AFTER id;',
            $schema->__toString()
        );
    }
}
