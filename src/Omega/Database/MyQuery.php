<?php

declare(strict_types=1);

namespace Omega\Database;

use Omega\Database\MyQuery\InnerQuery;
use Omega\Database\MyQuery\Table;

/**
 * Query Builder.
 */
class MyQuery
{
    public const int ORDER_ASC   = 0;
    public const int ORDER_DESC  = 1;

    /**
     * Create new Builder.
     *
     * @param MyPDO $PDO the PDO connection
     */
    public function __construct(protected MyPDO $PDO)
    {
    }

    /**
     * Create builder using invoke.
     *
     * @param string $table_name Table name
     *
     * @return Table
     */
    public function __invoke(string $table_name): Table
    {
        return $this->table($table_name);
    }

    /**
     * Create builder and set table name.
     *
     * @param string|InnerQuery $table_name Table name
     *
     * @return Table
     */
    public function table(string|InnerQuery $table_name): Table
    {
        return new Table($table_name, $this->PDO);
    }

    /**
     * Create Builder using static function.
     *
     * @param string|InnerQuery $table_name Table name
     * @param MyPDO             $PDO        The PDO connection, null give global instance
     *
     * @return Table
     */
    public static function from(string|InnerQuery $table_name, MyPDO $PDO): Table
    {
        $conn = new MyQuery($PDO);

        return $conn->table($table_name);
    }
}
