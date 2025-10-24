<?php

declare(strict_types=1);

namespace Omega\Database\Schema;

use Omega\Database\Schema\Table\Alter;
use Omega\Database\Schema\Table\Create as TableCreate;
use Omega\Database\Schema\Table\Raw;
use Omega\Database\Schema\Table\Truncate;

class Schema
{
    public function __construct(
        private readonly SchemaConnectionInterface $pdo,
        private ?string $databaseName = null,
    ) {
        $this->databaseName ??= $this->pdo->getDatabase();
    }

    /**
     * @return Create
     */
    public function create(): Create
    {
        return new Create($this->pdo, $this->databaseName);
    }

    /**
     * @return Drop
     */
    public function drop(): Drop
    {
        return new Drop($this->pdo, $this->databaseName);
    }

    /**
     * @param string $tableName
     * @return Truncate
     */
    public function refresh(string $tableName): Truncate
    {
        return new Truncate($this->databaseName, $tableName, $this->pdo);
    }

    /**
     * Create table schema.
     *
     * @param string                      $tableName Target table name
     * @param callable(TableCreate): void $blueprint
     * @return TableCreate
     */
    public function table(string $tableName, callable $blueprint): TableCreate
    {
        $columns = new TableCreate($this->databaseName, $tableName, $this->pdo);
        $blueprint($columns);

        return $columns;
    }

    /**
     * Update table structure.
     *
     * @param string                $tableName Target table name
     * @param callable(Alter): void $blueprint
     * @return Alter
     */
    public function alter(string $tableName, callable $blueprint): Alter
    {
        $columns       = new Alter($this->databaseName, $tableName, $this->pdo);
        $blueprint($columns);

        return $columns;
    }

    /**
     * Run raw table.
     *
     * @param string $raw
     * @return Raw
     */
    public function raw(string $raw): Raw
    {
        return new Raw($raw, $this->pdo);
    }
}
