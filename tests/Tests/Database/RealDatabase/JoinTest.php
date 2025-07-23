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

namespace Tests\Database\RealDatabase;

use Omega\Database\Query\Query;
use Omega\Database\Query\Join\InnerJoin;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabase;

/**
 * Test suite for SQL JOIN operations using the Query class and InnerJoin helper.
 *
 * This class validates that JOIN clauses work correctly in different types of
 * queries (SELECT, UPDATE, DELETE). It uses multiple related tables
 * (users, roles, logs) and verifies joined query behavior and results.
 *
 * The setup involves creating schemas, populating data, and executing queries
 * that simulate real-world relationships between entities.
 *
 * @category   Omega\Tests
 * @package    Databse
 * @subpackage RealDatabase
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Query::class)]
#[CoversClass(InnerJoin::class)]
class JoinTest extends AbstractDatabase
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
     * Creates the `users` table schema.
     *
     * Defines a user with id, name, email, and a foreign key reference to a role.
     *
     * @return bool True on successful creation, false otherwise.
     * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
     */
    private function createUsersSchema(): bool
    {
        return $this
            ->pdo
            ->query('CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                role_id INT NOT NULL
            )')
            ->execute();
    }

    /**
     * Creates the `roles` table schema.
     *
     * Defines a role with an id and unique role name.
     *
     * @return bool True on successful creation, false otherwise.
     * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
     */
    private function createRolesSchema(): bool
    {
        return $this
            ->pdo
            ->query('CREATE TABLE roles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                role_name VARCHAR(100) NOT NULL UNIQUE
            )')
            ->execute();
    }

    /**
     * Creates the `logs` table schema.
     *
     * Defines a log entry with a user reference, action, and creation timestamp.
     *
     * @return bool True on successful creation, false otherwise.
     * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
     */
    private function createLogsSchema(): bool
    {
        return $this
            ->pdo
            ->query('CREATE TABLE logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                action VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )')
            ->execute();
    }

    /**
     * @return void
     */
    private function factory(): void
    {
        $this->pdo
            ->query('INSERT INTO roles (role_name) VALUES
                ("Admin"),
                ("Editor"),
                ("Subscriber")')
            ->execute();

        $this->pdo
            ->query('INSERT INTO users (name, email, role_id) VALUES
                (\'Alice\', \'alice@example.com\', 1),
                (\'Bob\', \'bob@example.com\', 2),
                (\'Charlie\', \'charlie@example.com\', 3);')
        ->execute();

        $this->pdo
            ->query('INSERT INTO logs (user_id, action) VALUES
                (1, \'Logged In\'),
                (2, \'Logged In\'),
                (1, \'Deactivated\'),
                (3, \'Logged Out\');')
            ->execute();
    }

    /**
     * Test it can join in select query.
     *
     * @return void
     */
    public function testItCanJoinInSelectQuery(): void
    {
        $this->createUsersSchema();
        $this->createRolesSchema();
        $this->createLogsSchema();
        $this->factory();

        $users = Query::from('users', $this->pdo)
            ->select(['users.name', 'roles.role_name'])
            ->join(InnerJoin::ref('roles', 'role_id', 'id'))
            ->get();

        $this->assertEquals('Alice', $users[0]['name']);
        $this->assertEquals('Admin', $users[0]['role_name']);
    }

    /**
     * Test it can join in update query.
     *
     * @return void
     */
    public function testItCanJoinInUpdateQuery(): void
    {
        $this->createUsersSchema();
        $this->createRolesSchema();
        $this->createLogsSchema();
        $this->factory();

        Query::from('users', $this->pdo)
            ->update()
            ->value('name', 'Eve')
            ->join(InnerJoin::ref('roles', 'role_id', 'id'))
            ->equal('roles.role_name', 'Admin')
            ->execute();

        $users = $this->pdo->query('
            SELECT
                users.name, roles.role_name
            FROM users
            INNER JOIN roles ON
                users.role_id = roles.id
            ')
            ->resultSet();

        $this->assertEquals('Eve', $users[0]['name']);
        $this->assertEquals('Admin', $users[0]['role_name']);
    }

    /**
     * Test it can join in delete query.
     *
     * @return void
     */
    public function testItCanJoinInDeleteQuery(): void
    {
        $this->createUsersSchema();
        $this->createRolesSchema();
        $this->createLogsSchema();
        $this->factory();

        // Delete related logs first
        Query::from('logs', $this->pdo)
            ->delete()
            ->alias('l')
            ->join(InnerJoin::ref('users', 'user_id', 'id'))
            ->equal('users.role_id', 1) // Assuming role_id 1 is for 'Admin'
            ->execute();

        Query::from('users', $this->pdo)
            ->delete()
            ->alias('u')
            ->join(InnerJoin::ref('roles', 'role_id', 'id'))
            ->equal('roles.role_name', 'Admin')
            ->execute();

        $users = $this->pdo->query('
            SELECT
                users.name, roles.role_name
            FROM users
            INNER JOIN roles ON
                users.role_id = roles.id
            ')
            ->resultSet();

        $this->assertEquals('Bob', $users[0]['name']);
        $this->assertEquals('Editor', $users[0]['role_name']);
    }
}
