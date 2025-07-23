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

use Omega\Database\Connection;
use Omega\Database\Query\Insert;
use Omega\Database\Schema\Schema;
use Omega\Database\Schema\SchemaConnection;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Abstract base class for database-related tests.
 *
 * Provides connection setup, schema management, and utility methods
 * for creating and dropping test databases and tables.
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
abstract class AbstractDatabase extends TestCase
{
    /** @var array<string, string> Environment configuration for the database connection. */
    protected array $env;

    /** @var Connection PDO wrapper instance used for executing queries. */
    protected Connection $pdo;

    /** @var SchemaConnection Schema-aware PDO wrapper for schema operations. */
    protected SchemaConnection $pdo_schema;

    /** @var Schema Schema builder and manager instance. */
    protected Schema $schema;

    /**
     * Initialize the database connection and prepare schema.
     *
     * Creates a connection to the testing database and builds it if necessary.
     *
     * @return void
     * @throws \Exception
     */
    protected function createConnection(): void
    {
        $this->env = [
            'host'           => 'localhost',
            'user'           => 'root',
            'password'       => 'vb65ty4',
            'database_name'  => 'testing_db',
        ];

        $this->pdo_schema = new SchemaConnection($this->env);
        $this->schema     = new Schema($this->pdo_schema);

        // building the database
        $this->schema->create()->database('testing_db')->ifNotExists()->execute();

        $this->pdo        = new Connection($this->env);
    }

    /**
     * Drop the testing database schema.
     *
     * @return void
     */
    protected function dropConnection(): void
    {
        $this->schema->drop()->database('testing_db')->ifExists()->execute();
    }

    /**
     * Create the users table schema in the testing database.
     *
     * @return bool True if the table was created successfully, false otherwise.
     */
    protected function createUserSchema(): bool
    {
        return $this
           ->pdo
           ->query('CREATE TABLE users (
                user      varchar(32)  NOT NULL,
                password  varchar(500) NOT NULL,
                stat      int(2)       NOT NULL,
                PRIMARY KEY (user)
            )')
           ->execute();
    }

    /**
     * Insert multiple user rows into the users table.
     *
     * @param array<int, array<string, string|int|bool|null>> $users
     *   An array of associative arrays each containing keys 'user', 'password', and 'stat'.
     * @return bool True on successful insertion, false otherwise.
     */
    protected function createUser(array $users): bool
    {
        return (new Insert('users', $this->pdo))
            ->rows($users)
            ->execute();
    }
}
