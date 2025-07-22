<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

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
use Omega\Database\Schema\Table\Attributes\Alter\DataType;

/**
 * Class Alter
 *
 * Responsible for building and executing ALTER TABLE queries on an existing table.
 * It provides methods to add, modify, drop, and rename columns programmatically,
 * and composes the resulting SQL string dynamically.
 *
 * This class is typically used during database schema migrations to alter the structure
 * of existing tables.
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
class Alter extends AbstractSchema
{
    /**
     * Columns to be modified.
     *
     * @var Column[]|DataType[]
     */
    private array $alterColumns = [];

    /**
     * Columns to be added.
     *
     * @var Column[]|DataType[]
     */
    private array $addColumns = [];

    /**
     * Column names to be dropped.
     *
     * @var string[]
     */
    private array $dropColumns = [];

    /**
     * Columns to be renamed. Format: ['oldName' => 'newName']
     *
     * @var array<string, string>
     */
    private array $renameColumns = [];

    /**
     * Fully qualified table name (database.table).
     *
     * @var string
     */
    private string $tableName;

    /**
     * Create a new Alter instance.
     *
     * @param string           $databaseName The name of the database.
     * @param string           $tableName    The name of the table to alter.
     * @param SchemaConnection $pdo          Schema connection instance.
     */
    public function __construct(string $databaseName, string $tableName, SchemaConnection $pdo)
    {
        $this->tableName = $databaseName . '.' . $tableName;
        $this->pdo       = $pdo;
    }

    /**
     * Add a column (invokable shortcut for `column()`).
     *
     * @param string $columnName Name of the column to modify.
     * @return DataType
     */
    public function __invoke(string $columnName): DataType
    {
        return $this->column($columnName);
    }

    /**
     * Add a new column to the table.
     *
     * @param string $columnName Name of the new column.
     * @return DataType
     */
    public function add(string $columnName): DataType
    {
        return $this->addColumns[] = (new Column())->alterColumn($columnName);
    }

    /**
     * Drop a column from the table.
     *
     * @param string $columnName Name of the column to drop.
     * @return string The dropped column name.
     */
    public function drop(string $columnName): string
    {
        return $this->dropColumns[] = $columnName;
    }

    /**
     * Modify an existing column on the table.
     *
     * @param string $columnName Name of the column to modify.
     * @return DataType
     */
    public function column(string $columnName): DataType
    {
        return $this->alterColumns[] = (new Column())->alterColumn($columnName);
    }

    /**
     * Rename an existing column.
     *
     * @param string $from Original column name.
     * @param string $to   New column name.
     * @return string The new column name.
     */
    public function rename(string $from, string $to): string
    {
        return $this->renameColumns[$from] = $to;
    }

    /**
     * Build the final ALTER TABLE SQL query.
     *
     * @return string SQL query string.
     */
    protected function builder(): string
    {
        $query = [];

        // Merge and assemble all query parts
        $query = array_merge(
            $query,
            $this->getModify(),
            $this->getColumns(),
            $this->getDrops(),
            $this->getRename()
        );

        return "ALTER TABLE {$this->tableName} " . implode(', ', $query) . ';';
    }

    /**
     * Get SQL parts for modifying columns.
     *
     * @return string[] List of MODIFY COLUMN statements.
     */
    private function getModify(): array
    {
        $res = [];

        foreach ($this->alterColumns as $attribute) {
            $res[] = "MODIFY COLUMN {$attribute->__toString()}";
        }

        return $res;
    }

    /**
     * Get SQL parts for renaming columns.
     *
     * @return string[] List of RENAME COLUMN statements.
     */
    private function getRename(): array
    {
        $res = [];

        foreach ($this->renameColumns as $old => $new) {
            $res[] = "RENAME COLUMN {$old} TO {$new}";
        }

        return $res;
    }

    /**
     * Get SQL parts for adding new columns.
     *
     * @return string[] List of ADD COLUMN statements.
     */
    private function getColumns(): array
    {
        $res = [];

        foreach ($this->addColumns as $attribute) {
            $res[] = "ADD {$attribute->__toString()}";
        }

        return $res;
    }

    /**
     * Get SQL parts for dropping columns.
     *
     * @return string[] List of DROP COLUMN statements.
     */
    private function getDrops(): array
    {
        $res = [];

        foreach ($this->dropColumns as $drop) {
            $res[] = "DROP COLUMN {$drop}";
        }

        return $res;
    }
}
