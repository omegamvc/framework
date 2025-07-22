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

namespace Omega\Database\Schema;

use Omega\Database\Schema\Table\Alter;
use Omega\Database\Schema\Table\Create as CreateTable;
use Omega\Database\Schema\Table\Raw;
use Omega\Database\Schema\Table\Truncate;

/**
 * Entry point for managing database schemas.
 *
 * This class provides a fluent interface to perform common schema operations
 * such as creating, dropping, altering, refreshing tables, and executing raw SQL.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Schema
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class Schema
{
    /**
     * PDO schema connection instance.
     *
     * @var SchemaConnection
     */
    private SchemaConnection $pdo;

    /**
     * Initialize the schema manager.
     *
     * @param SchemaConnection $pdo The schema-specific PDO connection.
     */
    public function __construct(SchemaConnection $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Start a schema creation operation (database or table).
     *
     * @return Create
     */
    public function create(): Create
    {
        return new Create($this->pdo);
    }

    /**
     * Start a schema drop operation (database or table).
     *
     * @return Drop
     */
    public function drop(): Drop
    {
        return new Drop($this->pdo);
    }

    /**
     * Truncate the specified table (delete all rows).
     *
     * @param string $tableName The name of the table to truncate.
     * @return Truncate
     */
    public function refresh(string $tableName): Truncate
    {
        $databaseName = $this->pdo->getConfig()['database_name'];

        return new Truncate($databaseName, $tableName, $this->pdo);
    }

    /**
     * Create a new table using a blueprint.
     *
     * @param string                 $tableName The name of the table to create.
     * @param callable(CreateTable): void $blueprint The callback to define the table structure.
     * @return CreateTable
     */
    public function table(string $tableName, callable $blueprint): CreateTable
    {
        $databaseName = $this->pdo->getConfig()['database_name'];
        $columns      = new CreateTable($databaseName, $tableName, $this->pdo);
        $blueprint($columns);

        return $columns;
    }

    /**
     * Alter an existing table using a blueprint.
     *
     * @param string             $tableName The name of the table to alter.
     * @param callable(Alter): void $blueprint The callback to define the alterations.
     * @return Alter
     */
    public function alter(string $tableName, callable $blueprint): Alter
    {
        $databaseName = $this->pdo->getConfig()['database_name'];
        $columns      = new Alter($databaseName, $tableName, $this->pdo);
        $blueprint($columns);

        return $columns;
    }

    /**
     * Execute a raw SQL schema operation.
     *
     * @param string $raw The raw SQL string.
     * @return Raw
     */
    public function raw(string $raw): Raw
    {
        return new Raw($raw, $this->pdo);
    }
}
