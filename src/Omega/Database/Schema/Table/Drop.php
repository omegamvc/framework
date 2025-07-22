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

namespace Omega\Database\Schema\Table;

use Omega\Database\Schema\SchemaConnection;
use Omega\Database\Schema\AbstractSchema;
use Omega\Database\Schema\Traits\ConditionTrait;

/**
 * Class Drop
 *
 * Generates a SQL query to drop (delete) a table from a database.
 * It supports conditional execution using `IF EXISTS` or `IF NOT EXISTS`
 * through the included ConditionTrait.
 *
 * This class is used as part of a schema builder system to manage table removal
 * in a programmatic and safe way.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Schema\Table
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class Drop extends AbstractSchema
{
    use ConditionTrait;

    /**
     * Fully qualified table name in the format `database.table`.
     *
     * @var string
     */
    private string $tableName;

    /**
     * Drop constructor.
     *
     * @param string            $databaseName The name of the database.
     * @param string            $tableName    The name of the table to drop.
     * @param SchemaConnection  $pdo          The schema-level PDO connection.
     */
    public function __construct(string $databaseName, string $tableName, SchemaConnection $pdo)
    {
        $this->tableName = $databaseName . '.' . $tableName;
        $this->pdo       = $pdo;
    }

    /**
     * Build the DROP TABLE SQL statement.
     *
     * @return string The resulting SQL query string.
     */
    protected function builder(): string
    {
        $condition = $this->join([$this->ifExists, $this->tableName]);

        return 'DROP TABLE ' . $condition . ';';
    }
}
