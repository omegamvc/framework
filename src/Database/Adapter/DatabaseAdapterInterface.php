<?php

/**
 * Part of Omega - Database Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Database\Adapter;

use Pdo;
use Omega\Database\Migration\AbstractMigration;
use Omega\Database\QueryBuilder\AbstractQueryBuilder;

/**
 * Database Adapter Interface.
 *
 * The `DatabaseAdapterInterface` defines the contract for database
 * adapter classes, which provide a set of methods for interacting
 * with a database.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Adapter
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
interface DatabaseAdapterInterface
{
    /**
     * Get the underlying PDO instance for this connection.
     *
     * @return PDO Returns the current PDO instance that represents the database connection.
     */
    public function pdo(): PDO;

    /**
     * Check if database exists, otherwise create it.
     *
     * @param string $host     Holds the host of the database.
     * @param string $port     Holds the port number.
     * @param string $username Holds the username for the connection.
     * @param string $password Holds the password for the connection.
     * @return void
     */
    public function checkIfDatabaseExists(
        string $host,
        string $port,
        string $username,
        string $password
    ): void;

    /**
     * Start a new query builder on this connection.
     *
     * This method initializes a new query builder instance for building SQL queries.
     *
     * @return AbstractQueryBuilder An instance of the AbstractQueryBuilder class for constructing SQL queries.
     */
    public function query(): AbstractQueryBuilder;

    /**
     * Start a new migration to create a table on this connection.
     *
     * This method begins a new migration process for creating a database table.
     *
     * @param string $table The name of the table to create.
     * @return AbstractMigration Returns an instance of the AbstractMigration class for managing table creation.
     */
    public function createTable(string $table): AbstractMigration;

    /**
     * Start a new migration to modify an existing table on this connection.
     *
     * This method initiates a new migration process for modifying an existing database table.
     *
     * @param string $table The name of the table to modify.
     * @return AbstractMigration Returns an instance of the AbstractMigration class for managing table alterations.
     */
    public function alterTable(string $table): AbstractMigration;

    /**
     * Get the list of table names on this connection.
     *
     * Retrieve an array containing the names of all tables available on this database connection.
     *
     * @return string[] Returns an array of table names available on this connection.
     */
    public function getTables(): array;

    /**
     * Check if a table exists on this connection.
     *
     * Determine whether a specified table exists on the database connection.
     *
     * @param string $name The name of the table to check.
     * @return bool Returns true if the specified table exists, otherwise returns false.
     */
    public function hasTable(string $name): bool;

    /**
     * Drop all tables in the current database. Use with caution.
     *
     * This method is used to delete all tables in the current database. Exercise caution as data loss is irreversible.
     *
     * @return int|bool Returns 1 if all tables are successfully dropped, or false if any issues occur
     *                  during the process.
     */
    public function dropTables(): int|bool;
}
