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

namespace Tests\Database;

use PHPUnit\Framework\Attributes\CoversNothing;

use function password_hash;

use const PASSWORD_DEFAULT;

/**
 * Unit test class for verifying Connection logging behavior.
 *
 * This test case ensures that SQL queries executed through Connection are properly logged.
 * It uses a real temporary database connection for integration-style testing and validates
 * that queries such as SELECT and DELETE are tracked and returned in the correct order.
 *
 * The test uses a predefined `users` table and inserts a sample user before each test.
 * After each test, the database is dropped to maintain isolation.
 *
 * @category  Omega\Tests
 * @package   Database
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversNothing]
class ConnectionTest extends AbstractDatabase
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
        $this->createUser([
            [
                'user'     => 'giovannini',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'stat'     => 100,
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
     * Test it can log execution connection.
     *
     * @return void
     */
    public function testItCanLogExecutionConnection(): void
    {
        $this->pdo->flushLogs();
        $this->pdo->query('select * from users where user = :user')->bind('user', 'giovannini')->resultSet();
        $this->pdo->query('select * from users where user = :user')->bind('user', 'giovannini')->single();
        $this->pdo->query('delete from users where user = :user')->bind('user', 'giovannini')->execute();

        $logs = [
            'select * from users where user = :user',
            'select * from users where user = :user',
            'delete from users where user = :user',
        ];

        foreach ($this->pdo->getLogs() as $key => $log) {
            $this->assertEquals($log['query'], $logs[$key]);
        }
    }
}
