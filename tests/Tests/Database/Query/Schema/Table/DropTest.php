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

use Omega\Database\Schema\Table\Drop;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabaseQuery;

/**
 * Test suite for validating the SQL DROP TABLE generation logic.
 *
 * This class tests the Drop schema builder to ensure it correctly constructs
 * DROP TABLE statements under various conditional flags, such as IF EXISTS and
 * IF NOT EXISTS. It verifies both default and toggled behaviors of these modifiers,
 * providing coverage for all common drop scenarios.
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
#[CoversClass(Drop::class)]
class DropTest extends AbstractDatabaseQuery
{
    /**
     * Test it can create database.
     *
     * @return void
     */
    public function testItCanCreateDatabase(): void
    {
        $schema = new Drop('testing_db', 'test', $this->pdo_schema);

        $this->assertEquals(
            'DROP TABLE testing_db.test;',
            $schema->__toString()
        );
    }

    /**
     * Test it can create database if exists.
     *
     * @return void
     */
    public function testItCanCreateDatabaseIfExists(): void
    {
        $schema = new Drop('testing_db', 'test', $this->pdo_schema);

        $this->assertEquals(
            'DROP TABLE IF EXISTS testing_db.test;',
            $schema->ifExists()->__toString()
        );
    }

    /**
     * Test it can create database if exists false.
     *
     * @return void
     */
    public function testItCanCreateDatabaseIfExistsFalse(): void
    {
        $schema = new Drop('testing_db', 'test', $this->pdo_schema);

        $this->assertEquals(
            'DROP TABLE IF NOT EXISTS testing_db.test;',
            $schema->ifExists(false)->__toString()
        );
    }

    /**
     * Test it can create database if not exists.
     *
     * @return void
     */
    public function testItCanCreateDatabaseIfNotExists(): void
    {
        $schema = new Drop('testing_db', 'test', $this->pdo_schema);

        $this->assertEquals(
            'DROP TABLE IF NOT EXISTS testing_db.test;',
            $schema->ifNotExists()->__toString()
        );
    }

    /**
     * Test it can create database if not exists false.
     *
     * @return void
     */
    public function testItCanCreateDatabaseIfNotExistsFalse(): void
    {
        $schema = new Drop('testing_db', 'test', $this->pdo_schema);

        $this->assertEquals(
            'DROP TABLE IF EXISTS testing_db.test;',
            $schema->ifNotExists(false)->__toString()
        );
    }
}
