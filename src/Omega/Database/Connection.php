<?php

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

namespace Omega\Database;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

use function call_user_func;
use function is_bool;
use function is_int;
use function is_null;
use function microtime;
use function round;

/**
 * Class Connection
 *
 * Manages a PDO-based database connection and provides a simplified interface
 * for preparing, binding, executing, and retrieving query results.
 * Includes transaction handling and lightweight query logging with timing.
 *
 * @category  Omega
 * @package   Database
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class Connection
{
    /** @var PDO The PDO instance for database operations. */
    private PDO $pdo;

    /** @var PDOStatement The current prepared statement. */
    private PDOStatement $statement;

    /** @var array<string, string> Connection configuration such as host, dbname, user, and password. */
    protected array $config;

    /** @var string The last SQL query string prepared. */
    protected string $query;

    /** @var array<int, array<string, mixed>> Query logs containing SQL and execution time in milliseconds. */
    protected array $logs = [];

    /**
     * Creates a new database connection using the provided configuration array.
     *
     * @param array<string, string> $config Database connection parameters.
     * @return void
     * @throws Exception If the PDO connection fails.
     */
    public function __construct(array $config)
    {
        $databaseName = $config['database_name'];
        $host         = $config['host'];
        $user         = $config['user'];
        $pass         = $config['password'];

        $this->config = $config;
        $dsn           = "mysql:host=$host;dbname=$databaseName";
        $this->useDsn($dsn, $user, $pass);
    }

    /**
     * Returns the current instance (singleton pattern-like behavior).
     *
     * @return static
     */
    public function getInstance(): static
    {
        return $this;
    }

    /**
     * Establishes the actual PDO connection using DSN and credentials.
     *
     * @param string $dsn  The DSN string.
     * @param string $user Database username.
     * @param string $pass Database password.
     * @return $this
     * @throws Exception If PDO fails to connect.
     */
    protected function useDsn(string $dsn, string $user, string $pass): self
    {
        $option = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $option);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * Static factory to create a new connection.
     *
     * @param array<string, string> $config Connection parameters.
     * @return Connection
     * @throws Exception If connection fails.
     */
    public static function conn(array $config): Connection
    {
        return new self($config);
    }

    /**
     * Returns the database configuration used for the connection.
     *
     * @return array<string, string>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Prepares a SQL query for execution.
     *
     * @param string $query The raw SQL query.
     * @return $this
     */
    public function query(string $query): self
    {
        $this->statement = $this->pdo->prepare($this->query = $query);

        return $this;
    }

    /**
     * Binds a parameter to the prepared statement.
     *
     * @param bool|int|string|null $param The parameter placeholder or index.
     * @param mixed $value The value to bind.
     * @param bool|int|string|null $type (optional) The PDO parameter type.
     * @return $this
     */
    public function bind(bool|int|string|null $param, mixed $value, bool|int|string $type = null): self
    {
        if (is_null($type)) {
            $type = match (true) {
                is_int($value)  => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default         => PDO::PARAM_STR,
            };
        }
        $this->statement->bindValue($param, $value, $type);

        return $this;
    }

    /**
     * Executes the prepared statement and logs the query duration.
     *
     * @return bool True if the execution was successful.
     * @throws PDOException
     */
    public function execute(): bool
    {
        $start    = microtime(true);
        $execute  = $this->statement->execute();
        $elapsed  = round((microtime(true) - $start) * 1000, 2);

        $this->addLog($this->query, $elapsed);

        return $execute;
    }

    /**
     * Executes the prepared statement and returns all results as associative arrays.
     *
     * @return array|false The result set or false on failure.
     */
    public function resultSet(): array|false
    {
        $this->execute();

        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Executes the prepared statement and returns a single result row.
     *
     * @return mixed The fetched row as an associative array.
     */
    public function single(): mixed
    {
        $this->execute();

        return $this->statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Returns the number of rows affected by the last operation.
     *
     * @return int
     */
    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }

    /**
     * Returns the last inserted auto-increment ID from the connection.
     *
     * @return string|false
     */
    public function lastInsertId(): false|string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Wraps a callable within a transaction. Rolls back on failure.
     *
     * @param callable $callable The function to run within the transaction.
     * @return bool True if committed, false if rolled back.
     */
    public function transaction(callable $callable): bool
    {
        if (false === $this->beginTransaction()) {
            return false;
        }

        $return_call =  call_user_func($callable);
        if (false === $return_call) {
            return $this->cancelTransaction();
        }

        return $this->endTransaction();
    }

    /**
     * Begins a transaction.
     *
     * @return bool
     * @throws PDOException
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commits the current transaction.
     *
     * @return bool
     * @throws PDOException
     */
    public function endTransaction(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Rolls back the current transaction.
     *
     * @return bool
     * @throws PDOException
     */
    public function cancelTransaction(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Adds a log entry for a query and its execution time in milliseconds.
     *
     * @param string $query The SQL query string.
     * @param float  $elapsed_time Execution time in milliseconds.
     */
    protected function addLog(string $query, float $elapsed_time): void
    {
        $this->logs[] = [
            'query' => $query,
            'time'  => $elapsed_time,
        ];
    }

    /**
     * Clears the query log.
     *
     * @return void
     */
    public function flushLogs(): void
    {
        $this->logs = [];
    }

    /**
     * Returns the internal query log with timing data.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLogs(): array
    {
        return $this->logs;
    }
}
