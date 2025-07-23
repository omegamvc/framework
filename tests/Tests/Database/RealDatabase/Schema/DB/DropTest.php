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

namespace Tests\Database\RealDatabase\Schema\DB;

use Omega\Database\Schema\DB\Drop;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabase;

/**
 * Unit tests for the Drop class responsible for generating
 * SQL statements to drop databases.
 *
 * This test ensures that the Drop class correctly builds and executes
 * the SQL query required to drop an existing database.
 *
 * @category   Omega\Tests
 * @package    Database
 * @subpackage RealDatabase\Schema\DB
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Drop::class)]
class DropTest extends AbstractDatabase
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
     * Test it can generate create database.
     *
     * @return void
     */
    public function testItCanGenerateCreateDatabase(): void
    {
        $schema = new Drop($this->pdo_schema->getConfig()['database_name'], $this->pdo_schema);

        $this->assertTrue($schema->execute());
    }
}
