<?php

/**
 * Part of Omega - Database Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Database\Query;

use Omega\Database\Connection;

/**
 * Table query builder entry point.
 *
 * This class provides a convenient interface for executing various types of SQL queries
 * (INSERT, REPLACE, SELECT, UPDATE, DELETE) on a specific database table or subquery.
 * It acts as a factory that returns instances of query-specific builder classes.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Query
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class Table
{
    /**
     * PDO wrapper instance used to execute queries.
     *
     * @var Connection
     */
    protected Connection $pdo;

    /**
     * Target table name or subquery used for operations.
     *
     * @var string|InnerQuery
     */
    protected InnerQuery|string $tableName;

    /**
     * Initialize a new Table builder for a given table name or subquery.
     *
     * @param string|InnerQuery $tableName The name of the table or a subquery
     * @param Connection        $pdo       The database connection instance
     */
    public function __construct(string|InnerQuery $tableName, Connection $pdo)
    {
        $this->tableName = $tableName;
        $this->pdo       = $pdo;
    }

    /**
     * Create an INSERT query builder.
     *
     * @return Insert The insert query builder
     */
    public function insert(): Insert
    {
        return new Insert($this->tableName, $this->pdo);
    }

    /**
     * Create a REPLACE query builder.
     *
     * @return Replace The replace query builder
     */
    public function replace(): Replace
    {
        return new Replace($this->tableName, $this->pdo);
    }

    /**
     * Create a SELECT query builder.
     *
     * @param string[] $selectColumns List of column names to select (default is ['*'])
     * @return Select The select query builder
     */
    public function select(array $selectColumns = ['*']): Select
    {
        return new Select($this->tableName, $selectColumns, $this->pdo);
    }

    /**
     * Create an UPDATE query builder.
     *
     * @return Update The update query builder
     */
    public function update(): Update
    {
        return new Update($this->tableName, $this->pdo);
    }

    /**
     * Create a DELETE query builder.
     *
     * @return Delete The delete query builder
     */
    public function delete(): Delete
    {
        return new Delete($this->tableName, $this->pdo);
    }

    /**
     * Retrieve metadata information about the current table.
     *
     * This includes column names, types, character sets, nullability, etc.
     *
     * @return array<string, mixed> The metadata information, or an empty array if no result
     */
    public function info(): array
    {
        $this->pdo->query(
            'SELECT
                COLUMN_NAME,
                COLUMN_TYPE,
                CHARACTER_SET_NAME,
                COLLATION_NAME,
                IS_NULLABLE,
                ORDINAL_POSITION,
                COLUMN_KEY
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = :dbs AND TABLE_NAME = :table'
        );
        $this->pdo->bind(':table', $this->tableName);
        $this->pdo->bind(':dbs', $this->pdo->getConfig()['database_name']);

        $result = $this->pdo->resultSet();

        return $result === false ? [] : $result;
    }
}
