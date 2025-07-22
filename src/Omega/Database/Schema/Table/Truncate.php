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
 * Class Truncate
 *
 * Builds a SQL TRUNCATE TABLE query with optional IF EXISTS or IF NOT EXISTS conditions.
 * Used to remove all rows from a specified table without logging individual row deletions.
 * Typically faster than DELETE for large datasets, but cannot be rolled back in many engines.
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
class Truncate extends AbstractSchema
{
    use ConditionTrait;

    /**
     * Fully qualified table name (e.g., "database.table") to truncate.
     *
     * @var string
     */
    private string $tableName;

    /**
     * Truncate constructor.
     *
     * @param string            $databaseName Name of the target database.
     * @param string            $tableName    Name of the table to truncate.
     * @param SchemaConnection  $pdo          Schema-level PDO connection instance.
     */
    public function __construct(string $databaseName, string $tableName, SchemaConnection $pdo)
    {
        $this->tableName = $databaseName . '.' . $tableName;
        $this->pdo       = $pdo;
    }

    /**
     * Builds the full SQL TRUNCATE TABLE statement.
     *
     * @return string SQL statement to be executed.
     */
    protected function builder(): string
    {
        $condition = $this->join([$this->ifExists, $this->tableName]);

        return 'TRUNCATE TABLE ' . $condition . ';';
    }
}
