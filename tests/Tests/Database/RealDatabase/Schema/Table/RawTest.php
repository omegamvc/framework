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

use Omega\Database\Schema\Table\Raw;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabase;

/**
 * Unit test for the Raw class, which allows execution of raw SQL statements
 * directly against the database schema.
 *
 * This test ensures that raw SQL commands (such as CREATE TABLE)
 * can be executed correctly using the Raw class.
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
#[CoversClass(Raw::class)]
class RawTest extends AbstractDatabase
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
     * Test it can execute raw create table statement.
     *
     * @return void
     */
    public function testItCanExecuteRawCreateTableStatement(): void
    {
        $schema = new Raw('CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (PersonID) )', $this->pdo_schema);

        $this->assertTrue($schema->execute());
    }
}
