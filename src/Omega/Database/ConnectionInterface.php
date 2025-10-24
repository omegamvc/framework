<?php

declare(strict_types=1);

namespace Omega\Database;

use PDOException;

interface ConnectionInterface extends LoggerInterface, TransactionInterface
{
    /**
     * Preparing a statement in the query.
     *
     * @param string $query
     * @return self
     */
    public function query(string $query): self;

    /**
     * Replace the user's input parameter with a placeholder.
     *
     * @param string|int|bool|null $param
     * @param mixed                $value
     * @param string|int|bool|null $type
     * @return self
     */
    public function bind(string|int|bool|null $param, mixed $value, string|int|bool|null $type = null): self;

    /**
     * Executes a prepared statement (query).
     *
     * @return bool
     * @throws PDOException
     */
    public function execute(): bool;

    /**
     * Returns the results of the query executed in the form of an array.
     *
     * @return array|false
     */
    public function resultset(): array|false;

    /**
     * Returns the results of the query, displaying only one row of data.
     *
     * @return mixed
     */
    public function single(): mixed;

    /**
     * Displays the amount of data that has been successfully saved, changed, or deleted.
     *
     * @return int
     */
    public function rowCount(): int;

    /**
     * ID from the last saved data.
     *
     * @return string|false
     */
    public function lastInsertId(): string|false;
}
