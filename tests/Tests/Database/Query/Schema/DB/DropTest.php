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

use Omega\Database\Schema\DB\Drop;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabaseQuery;

/**
 * Test suite for the Drop schema class.
 *
 * This class validates the behavior of the Drop class used to generate SQL
 * statements for dropping databases, including support for IF EXISTS and IF NOT EXISTS clauses.
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
#[CoversClass(Drop::class)]
class DropTest extends AbstractDatabaseQuery
{
    /**
     * Test that it generates a basic DROP DATABASE statement.
     *
     * @return void
     */
    public function testDropsDatabase(): void
    {
        $schema = new Drop('test', $this->pdo_schema);

        $this->assertEquals(
            'DROP DATABASE test;',
            $schema->__toString()
        );
    }

    /**
     * Test that it generates DROP DATABASE with IF EXISTS clause.
     *
     * @return void
     */
    public function testDropsDatabaseWithIfExists(): void
    {
        $schema = new Drop('test', $this->pdo_schema);

        $this->assertEquals(
            'DROP DATABASE IF EXISTS test;',
            $schema->ifExists()->__toString()
        );
    }

    /**
     * Test that it generates DROP DATABASE without IF EXISTS clause.
     *
     * @return void
     */
    public function testDropsDatabaseWithoutIfExists(): void
    {
        $schema = new Drop('test', $this->pdo_schema);

        $this->assertEquals(
            'DROP DATABASE IF NOT EXISTS test;',
            $schema->ifExists(false)->__toString()
        );
    }

    /**
     * Test that it generates DROP DATABASE with IF NOT EXISTS clause.
     *
     * @return void
     */
    public function testDropsDatabaseWithIfNotExists(): void
    {
        $schema = new Drop('test', $this->pdo_schema);

        $this->assertEquals(
            'DROP DATABASE IF NOT EXISTS test;',
            $schema->ifNotExists()->__toString()
        );
    }

    /**
     * Test that it generates DROP DATABASE without IF NOT EXISTS clause.
     *
     * @return void
     */
    public function testDropsDatabaseWithoutIfNotExists(): void
    {
        $schema = new Drop('test', $this->pdo_schema);

        $this->assertEquals(
            'DROP DATABASE IF EXISTS test;',
            $schema->ifNotExists(false)->__toString()
        );
    }
}
