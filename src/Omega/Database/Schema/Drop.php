<?php

declare(strict_types=1);

namespace Omega\Database\Schema;

use function array_pad;
use function explode;

/**
 * Proxy for drop database and table
 */
readonly class Drop
{
    public function __construct(
        private SchemaConnectionInterface $pdo,
        private ?string $databaseName = null,
    ) {
    }

    /**
     * Drop database.
     *
     * @param string $databaseName
     * @return DB\Drop
     */
    public function database(string $databaseName): DB\Drop
    {
        return new DB\Drop($databaseName, $this->pdo);
    }

    /**
     * Drop table.
     *
     * @param string $tableName
     * @return Table\Drop
     */
    public function table(string $tableName): Table\Drop
    {
        [$database, $table] = array_pad(explode('.', $tableName, 2), 2, null);
        $database           = $database ?: $this->databaseName;
        $table              = $table ?: $tableName;

        return new Table\Drop($database, $table, $this->pdo);
    }
}
