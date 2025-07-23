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

use Omega\Database\Schema\Table\Drop;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabase;
use Tests\Database\Traits\UserTrait;

/**
 * Unit test for the Drop class, which is responsible for generating
 * SQL DROP statements to delete database objects such as tables or databases.
 *
 * This test ensures that the Drop class can correctly build and execute
 * a DROP statement against the given database schema.
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
#[CoversClass(Drop::class)]
class DropTest extends AbstractDatabase
{
    use UserTrait;

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
        $this->createUser([
            [
                'user'     => 'taylor',
                'password' => 'secret',
                'stat'     => 99,
            ],
        ]);
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
     * Test it can generate drop database.
     *
     * @return void
     */
    public function testItCanGenerateDropDatabase(): void
    {
        $schema = new Drop($this->pdo_schema->getConfig()['database_name'], 'users', $this->pdo_schema);

        $this->assertTrue($schema->execute());
    }
}
