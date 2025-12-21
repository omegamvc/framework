<?php

declare(strict_types=1);

namespace Omega\Database\Query;

use Omega\Database\Connection;
use Omega\Database\ConnectionInterface;

/**
 * Query Builder.
 */
class Query
{
    /** @var int  */
    public const int ORDER_ASC   = 0;

    /** @var int */
    public const int ORDER_DESC  = 1;

    /**
     * Create new Builder.
     *
     * @param ConnectionInterface $pdo the PDO connection
     */
    public function __construct(protected ConnectionInterface $pdo)
    {
    }

    /**
     * Create builder using invoke.
     *
     * @param string $tableName Table name
     * @return Table
     */
    public function __invoke(string $tableName): Table
    {
        return $this->table($tableName);
    }

    /**
     * Create builder and set table name.
     *
     * @param string|InnerQuery $tableName Table name
     * @return Table
     */
    public function table(string|InnerQuery $tableName): Table
    {
        return new Table($tableName, $this->pdo);
    }

    /**
     * Create Builder using static function.
     *
     * @param string|InnerQuery $tableName Table name
     * @param Connection        $pdo       The PDO connection, null give global instance
     * @return Table
     */
    public static function from(string|InnerQuery $tableName, ConnectionInterface $pdo): Table
    {
        $conn = new Query($pdo);

        return $conn->table($tableName);
    }
}
