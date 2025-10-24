<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

declare(strict_types=1);

namespace Omega\Database\Schema\Table;

use Omega\Database\Schema\Query;
use Omega\Database\Schema\Table\Attributes\Alter\DataType;
use Omega\Database\Schema\SchemaConnectionInterface;

use function array_merge;
use function implode;

class Alter extends Query
{
    /** @var Column[]|DataType[] */
    private array $alterColumns = [];

    /** @var Column[]|DataType[] */
    private array $addColumns = [];

    /** @var string[] */
    private array $dropColumns = [];

    /** @var array<string, string> */
    private array $renameColumns = [];

    /** @var string */
    private string $tableName;

    /**
     * @param string                    $databaseName
     * @param string                    $tableName
     * @param SchemaConnectionInterface $pdo
     */
    public function __construct(string $databaseName, string $tableName, SchemaConnectionInterface $pdo)
    {
        $this->tableName = $databaseName . '.' . $tableName;
        $this->pdo       = $pdo;
    }

    /**
     * Add new column to the exist table.
     *
     * @param string $columnName
     * @return DataType
     */
    public function __invoke(string $columnName): DataType
    {
        return $this->column($columnName);
    }

    /**
     * @param string $columnName
     * @return DataType
     */
    public function add(string $columnName): DataType
    {
        return $this->addColumns[] = new Column()->alterColumn($columnName);
    }

    /**
     * @param string $columnName
     * @return string
     */
    public function drop(string $columnName): string
    {
        return $this->dropColumns[] = $columnName;
    }

    /**
     * @param string $columnName
     * @return DataType
     */
    public function column(string $columnName): DataType
    {
        return $this->alterColumns[] = new Column()->alterColumn($columnName);
    }

    /**
     * @param string $from
     * @param string $to
     * @return string
     */
    public function rename(string $from, string $to): string
    {
        return $this->renameColumns[$from] = $to;
    }

    /**
     * @return string
     */
    protected function builder(): string
    {
        $query = [];

        // merge alter, add, drop, rename
        $query = array_merge($query, $this->getModify(), $this->getColumns(), $this->getDrops(), $this->getRename());
        $query = implode(', ', $query);

        return "ALTER TABLE {$this->tableName} {$query};";
    }

    /**
     * @return string[]
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
     * @return string[]
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
     * @return string[]
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
     * @return string[]
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
