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

namespace Tests\Database\Query\Schema\DB;

use Omega\Database\Schema\DB\Create;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabaseQuery;

/**
 * Test suite for the Create schema class.
 *
 * This class verifies the behavior of the Create class used to generate SQL
 * statements for creating databases, with support for IF EXISTS and IF NOT EXISTS clauses.
 *
 * @category   Omega\Tests
 * @package    Database
 * @subpackage Schema\Db
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Create::class)]
class CreateTest extends AbstractDatabaseQuery
{
    /**
     * Test that it generates a basic CREATE DATABASE statement.
     *
     * @return void
     */
    public function testCreatesDatabase(): void
    {
        $schema = new Create('test', $this->pdo_schema);

        $this->assertEquals(
            'CREATE DATABASE test;',
            $schema->__toString()
        );
    }

    /**
     * Test that it generates CREATE DATABASE with IF EXISTS clause.
     *
     * @return void
     */
    public function testCreatesDatabaseWithIfExists(): void
    {
        $schema = new Create('test', $this->pdo_schema);

        $this->assertEquals(
            'CREATE DATABASE IF EXISTS test;',
            $schema->ifExists(true)->__toString()
        );
    }

    /**
     * Test that it generates CREATE DATABASE without IF EXISTS clause.
     *
     * @return void
     */
    public function testCreatesDatabaseWithoutIfExists(): void
    {
        $schema = new Create('test', $this->pdo_schema);

        $this->assertEquals(
            'CREATE DATABASE IF NOT EXISTS test;',
            $schema->ifExists(false)->__toString()
        );
    }

    /**
     * Test that it generates CREATE DATABASE with IF NOT EXISTS clause.
     *
     * @return void
     */
    public function testCreatesDatabaseWithIfNotExists(): void
    {
        $schema = new Create('test', $this->pdo_schema);

        $this->assertEquals(
            'CREATE DATABASE IF NOT EXISTS test;',
            $schema->ifNotExists(true)->__toString()
        );
    }

    /**
     * Test that it generates CREATE DATABASE without IF NOT EXISTS clause.
     *
     * @return void
     */
    public function testCreatesDatabaseWithoutIfNotExists(): void
    {
        $schema = new Create('test', $this->pdo_schema);

        $this->assertEquals(
            'CREATE DATABASE IF EXISTS test;',
            $schema->ifNotExists(false)->__toString()
        );
    }
}
