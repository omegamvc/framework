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

namespace Omega\Database\Schema\DB;

use Omega\Database\Schema\SchemaConnection;
use Omega\Database\Schema\AbstractSchema;
use Omega\Database\Schema\Traits\ConditionTrait;

/**
 * Class Create
 *
 * Builds a SQL statement to create a new database.
 * Optionally supports conditional creation with "IF NOT EXISTS"
 * via the ConditionTrait.
 *
 * Example:
 *     CREATE DATABASE IF NOT EXISTS my_database;
 *
 * This class is typically used by schema managers to dynamically
 * generate and execute schema-level operations.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Schema\DB
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class Create extends AbstractSchema
{
    use ConditionTrait;

    /**
     * The name of the database to be created.
     *
     * @var string
     */
    private string $databaseName;

    /**
     * Create constructor.
     *
     * Initializes the Create schema builder with the target database
     * name and a SchemaConnection instance.
     *
     * @param string           $databaseName The name of the database to create
     * @param SchemaConnection $pdo          Connection to the database server (not a specific database)
     */
    public function __construct(string $databaseName, SchemaConnection $pdo)
    {
        $this->databaseName = $databaseName;
        $this->pdo          = $pdo;
    }

    /**
     * Builds the SQL query string for creating the database.
     * Adds conditional clauses if set (e.g., IF NOT EXISTS).
     *
     * @return string The complete CREATE DATABASE SQL statement
     */
    protected function builder(): string
    {
        $condition = $this->join([$this->ifExists, $this->databaseName]);

        return 'CREATE DATABASE ' . $condition . ';';
    }
}
