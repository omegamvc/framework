<?php

declare(strict_types=1);

namespace Omega\Database\Schema;

/** Proxy for create database and table */
readonly class Create
{
    public function __construct(
        private SchemaConnectionInterface $pdo,
        private ?string $databaseName = null,
    ) {
    }

    /**
     * Create database.
     */
    public function database(string $databaseName): DB\Create
    {
        return new DB\Create($databaseName, $this->pdo);
    }

    /**
     * Create table.
     */
    public function table(string $tableName): Table\Create
    {
        return new Table\Create($this->databaseName, $tableName, $this->pdo);
    }
}
