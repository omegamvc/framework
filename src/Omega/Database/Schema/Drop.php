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
 * Entry point for dropping database or table schema definitions.
 *
 * This class provides methods to initialize schema builders
 * for dropping databases and tables, delegating the logic
 * to specialized classes in the DB and Table namespaces.
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
class Drop
{
    /**
     * PDO connection instance for schema operations.
     *
     * @var SchemaConnection
     */
    private SchemaConnection $pdo;

    /**
     * Create a new Drop schema builder.
     *
     * @param SchemaConnection $pdo The schema connection instance.
     */
    public function __construct(SchemaConnection $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Create a database drop schema builder.
     *
     * @param string $databaseName The name of the database to drop.
     * @return DB\Drop A database drop schema object.
     */
    public function database(string $databaseName): DB\Drop
    {
        return new DB\Drop($databaseName, $this->pdo);
    }

    /**
     * Create a table drop schema builder.
     *
     * @param string $tableName The name of the table to drop.
     * @return Table\Drop A table drop schema object.
     */
    public function table(string $tableName): Table\Drop
    {
        $databaseName = $this->pdo->getConfig()['database_name'];

        return new Table\Drop($databaseName, $tableName, $this->pdo);
    }
}
