<?php

declare(strict_types=1);

namespace Omega\Database\Query;

use Omega\Database\ConnectionInterface;

class Table
{
    /** @var ConnectionInterface Connection instance. */
    protected ConnectionInterface $pdo;

    /** @var string|InnerQuery Table name. */
    protected string|InnerQuery $tableName;

    /**
     * @param string|InnerQuery   $tableName Table name
     * @param ConnectionInterface $pdo        ConnectionInstance
     */
    public function __construct(string|InnerQuery $tableName, ConnectionInterface $pdo)
    {
        $this->tableName = $tableName;
        $this->pdo       = $pdo;
    }

    /**
     * Perform insert query.
     *
     * @return Insert
     */
    public function insert(): Insert
    {
        return new Insert($this->tableName, $this->pdo);
    }

    /**
     * Perform replace query.
     *
     * @return Replace
     */
    public function replace(): Replace
    {
        return new Replace($this->tableName, $this->pdo);
    }

    /**
     * Perform select query.
     *
     * @param string[] $selectColumns Selected column (raw)
     * @return Select
     */
    public function select(array $selectColumns = ['*']): Select
    {
        return new Select($this->tableName, $selectColumns, $this->pdo);
    }

    /**
     * Perform update query.
     *
     * @return Update
     */
    public function update(): Update
    {
        return new Update($this->tableName, $this->pdo);
    }

    /**
     * Perform delete query.
     *
     * @return Delete
     */
    public function delete(): Delete
    {
        return new Delete($this->tableName, $this->pdo);
    }

    /**
     * Get table information.
     *
     * @return array<string, mixed>
     */
    public function info(): array
    {
        $this->pdo->query(
            'SELECT
                COLUMN_NAME,
                COLUMN_TYPE,
                CHARACTER_SET_NAME,
                COLLATION_NAME,
                IS_NULLABLE,
                ORDINAL_POSITION,
                COLUMN_KEY
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table'
        );
        $this->pdo->bind(':table', $this->tableName);

        $result = $this->pdo->resultset();

        return $result === false ? [] : $result;
    }
}
