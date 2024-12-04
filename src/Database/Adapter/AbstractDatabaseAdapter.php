<?php

/**
 * Part of Omega - Database Package.
 *
 * @see       https://omegamvc.github.io
 *
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Database\Adapter;

/*
 * @use
 */
use function in_array;
use function extension_loaded;
use Omega\Database\Exception\AdapterException;
use Omega\Database\Exception\ConnectionException;
use Omega\Database\Migration\AbstractMigration;
use Omega\Database\QueryBuilder\AbstractQueryBuilder;
use Pdo;
use PDOException;

/**
 * Abstract database adapter class.
 *
 * The `AbstractDatabaseAdapter` is designed to provide a basic abstraction for database
 * connection and management. This class is declared as abstract and offers a basic implementation
 * of several methods defined in the `DatabaseAdapterInterface` interface.
 *
 * @category    Omega
 * @package     Database
 * @subpackage  Adapter
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
abstract class AbstractDatabaseAdapter implements DatabaseAdapterInterface
{
    /**
     * Pdo instance.
     *
     * @var Pdo Holds an instance of Pdo.
     */
    public Pdo $pdo;

    /**
     * Constructs a new database adapter instance.
     *
     * @param string $dsn      The Data Source Name (DSN) for the database connection.
     * @param string $username The username for the database connection.
     * @param string $password The password for the database connection.
     *
     * @throws AdapterException    if the PDO extension is not installed or enabled.
     * @throws ConnectionException if the database connection fails.
     * @throws PDOException
     */
    public function __construct(string $dsn, string $username = '', string $password = '')
    {
        if (! extension_loaded('pdo')) {
            throw new AdapterException(
                'PDO extension is not enabled. Please make sure to install or enable the PDO extension to 
                            use database functionality.'
            );
        }

        try {
            $this->pdo = new Pdo($dsn, $username, $password);
        } catch (PDOException $e) {
            throw new ConnectionException(
                'Database connection failed: '
                . $e->getMessage()
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return Pdo Returns the current PDO instance that represents the database connection.
     *
     * @throws AdapterException if pdo extension is not installed or not enabled.
     */
    public function pdo(): Pdo
    {
        return $this->pdo;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $host     Holds the host of the database.
     * @param string $port     Holds the port number.
     * @param string $username Holds the username for the connection.
     * @param string $password Holds the password for the connection.
     *
     * @return void
     */
    abstract public function checkIfDatabaseExists(
        string $host,
        string $port,
        string $username,
        string $password
    ): void;

    /**
     * {@inheritdoc}
     *
     * @param string $name The name of the table to check.
     *
     * @return bool Returns true if the specified table exists, otherwise returns false.
     */
    public function hasTable(string $name): bool
    {
        $tables = $this->getTables();

        return in_array($name, $tables);
    }

    /**
     * {@inheritdoc}
     *
     * @return AbstractQueryBuilder An instance of the AbstractQueryBuilder class for constructing SQL queries.
     */
    abstract public function query(): AbstractQueryBuilder;

    /**
     * {@inheritdoc}
     *
     * @param string $table The name of the table to create.
     *
     * @return AbstractMigration Returns an instance of the AbstractMigration class for managing table creation.
     */
    abstract public function createTable(string $table): AbstractMigration;

    /**
     * {@inheritdoc}
     *
     * @param string $table The name of the table to modify.
     *
     * @return AbstractMigration Returns an instance of the AbstractMigration class for managing table alterations.
     */
    abstract public function alterTable(string $table): AbstractMigration;

    /**
     * {@inheritdoc}
     *
     * @return string[] Returns an array of table names available on this connection.
     */
    abstract public function getTables(): array;

    /**
     * {@inheritdoc}
     *
     * @return int|bool Returns 1 if all tables are successfully dropped, or false if any issues occur
     *                  during the process.
     */
    abstract public function dropTables(): int|bool;
}
