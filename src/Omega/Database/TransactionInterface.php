<?php

declare(strict_types=1);

namespace Omega\Database;

interface TransactionInterface
{
    /**
     * @param callable(): bool $callable
     *
     * @return bool Transaction status
     */
    public function transaction(callable $callable): bool;

    /**
     * Initiates a transaction.
     *
     * @return bool True if success
     *
     * @throws \PDOException *
     */
    public function beginTransaction(): bool;

    /**
     * Commits a transaction.
     *
     * @return bool True if success
     *
     * @throws \PDOException
     */
    public function endTransaction(): bool;

    /*
     * Rolls back a transaction.
     *
     * @return bool True if success
     *
     * @throws \PDOException
     */
    public function cancelTransaction(): bool;
}
