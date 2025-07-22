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

/**
 * Entry point for creating database or table schema definitions.
 *
 * This class acts as a factory for database and table creation objects,
 * delegating the actual schema building to specialized classes in the
 * DB and Table namespaces.
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
class Create
{
    /**
     * PDO connection instance for schema operations.
     *
     * @var SchemaConnection
     */
    private SchemaConnection $pdo;

    /**
     * Create a new Create schema builder.
     *
     * @param SchemaConnection $pdo The schema connection instance.
     */
    public function __construct(SchemaConnection $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Create a database schema builder.
     *
     * @param string $databaseName The name of the database to create.
     * @return DB\Create A database creation schema object.
     */
    public function database(string $databaseName): DB\Create
    {
        return new DB\Create($databaseName, $this->pdo);
    }

    /**
     * Create a table schema builder.
     *
     * @param string $tableName The name of the table to create.
     * @return Table\Create A table creation schema object.
     */
    public function table(string $tableName): Table\Create
    {
        $databaseName = $this->pdo->getConfig()['database_name'];

        return new Table\Create($databaseName, $tableName, $this->pdo);
    }
}
