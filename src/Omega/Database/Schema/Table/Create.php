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
use Omega\Database\Schema\Table\Attributes\DataType;

use function array_map;
use function array_merge;
use function count;
use function implode;

/**
 * Class Create
 *
 * Builds a SQL query to create a new table schema in a specific database.
 * This class supports defining columns, primary keys, unique constraints,
 * storage engine, and character set configuration.
 *
 * It extends AbstractSchema and provides a fluent interface for programmatically
 * defining a CREATE TABLE statement.
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
class Create extends AbstractSchema
{
    /**
     * Available storage engine constants.
     */
    public const string INNODB    = 'INNODB';
    public const string MYISAM    = 'MYISAM';
    public const string MEMORY    = 'MEMORY';
    public const string MERGE     = 'MERGE';
    public const string EXAMPLE   = 'EXAMPLE';
    public const string ARCHIVE   = 'ARCHIVE';
    public const string CSV       = 'CSV';
    public const string BLACKHOLE = 'BLACKHOLE';
    public const string FEDERATED = 'FEDERATED';

    /**
     * List of columns to be created in the table.
     *
     * @var Column[]|DataType[]
     */
    private array $columns;

    /**
     * List of primary key columns.
     *
     * @var string[]
     */
    private array $primaryKeys;

    /**
     * List of unique columns.
     *
     * @var string[]
     */
    private array $uniques;

    /**
     * The storage engine to use (e.g., InnoDB, MyISAM).
     *
     * @var string
     */
    private string $storeEngine;

    /**
     * The character set to use (e.g., utf8mb4).
     *
     * @var string
     */
    private string $characterSet;

    /**
     * Fully qualified table name in the format `database.table`.
     *
     * @var string
     */
    private string $tableName;

    /**
     * Create constructor.
     *
     * @param string            $databaseName The name of the database.
     * @param string            $tableName    The name of the table to create.
     * @param SchemaConnection  $pdo          The PDO schema connection instance.
     */
    public function __construct(string $databaseName, string $tableName, SchemaConnection $pdo)
    {
        $this->tableName    = $databaseName . '.' . $tableName;
        $this->pdo          = $pdo;
        $this->columns      = [];
        $this->primaryKeys  = [];
        $this->uniques      = [];
        $this->storeEngine  = '';
        $this->characterSet = '';
    }

    /**
     * Add a new column using callable syntax.
     *
     * @param string $columnName
     * @return DataType
     */
    public function __invoke(string $columnName): DataType
    {
        return $this->columns[] = (new Column())->column($columnName);
    }

    /**
     * Add a new column using explicit call.
     *
     * @return Column
     */
    public function addColumn(): Column
    {
        return $this->columns[] = new Column();
    }

    /**
     * Set multiple columns.
     *
     * @param Column[] $columns
     * @return $this
     */
    public function columns(array $columns): self
    {
        $this->columns = [];
        foreach ($columns as $column) {
            $this->columns[] = $column;
        }

        return $this;
    }

    /**
     * Define a primary key column.
     *
     * @param string $columnName
     * @return $this
     */
    public function primaryKey(string $columnName): self
    {
        $this->primaryKeys[] = $columnName;

        return $this;
    }

    /**
     * Define a unique constraint on a column.
     *
     * @param string $unique
     * @return $this
     */
    public function unique(string $unique): self
    {
        $this->uniques[] = $unique;

        return $this;
    }

    /**
     * Set the storage engine.
     *
     * @param string $engine
     * @return $this
     */
    public function engine(string $engine): self
    {
        $this->storeEngine = $engine;

        return $this;
    }

    /**
     * Set the character set.
     *
     * @param string $characterSet
     * @return $this
     */
    public function character(string $characterSet): self
    {
        $this->characterSet = $characterSet;

        return $this;
    }

    /**
     * Build the final CREATE TABLE SQL statement.
     *
     * @return string
     */
    protected function builder(): string
    {
        $columns = array_merge($this->getColumns(), $this->getPrimaryKey(), $this->getUnique());
        $columns = $this->join($columns, ', ');
        $query   = $this->join([$this->tableName, '(', $columns, ')' . $this->getStoreEngine() . $this->getCharacterSet()]);

        return 'CREATE TABLE ' . $query;
    }

    /**
     * Generate SQL for column definitions.
     *
     * @return string[]
     */
    private function getColumns(): array
    {
        $res = [];

        foreach ($this->columns as $attribute) {
            $res[] = $attribute->__toString();
        }

        return $res;
    }

    /**
     * Generate SQL for the primary key clause.
     *
     * @return string[]
     */
    private function getPrimaryKey(): array
    {
        if (count($this->primaryKeys) === 0) {
            return [''];
        }

        $primaryKeys = array_map(fn ($primaryKey) => $primaryKey, $this->primaryKeys);
        $primaryKeys = implode(', ', $primaryKeys);

        return ["PRIMARY KEY ({$primaryKeys})"];
    }

    /**
     * Generate SQL for the unique constraint clause.
     *
     * @return string[]
     */
    private function getUnique(): array
    {
        if (count($this->uniques) === 0) {
            return [''];
        }

        $uniques = implode(', ', $this->uniques);

        return ["UNIQUE ({$uniques})"];
    }

    /**
     * Return the SQL clause for storage engine if set.
     *
     * @return string
     */
    private function getStoreEngine(): string
    {
        return $this->storeEngine === '' ? '' : ' ENGINE=' . $this->storeEngine;
    }

    /**
     * Return the SQL clause for character set if set.
     *
     * @return string
     */
    private function getCharacterSet(): string
    {
        return $this->characterSet === '' ? '' : " CHARACTER SET {$this->characterSet}";
    }
}
