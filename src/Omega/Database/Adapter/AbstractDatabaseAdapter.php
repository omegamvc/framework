<?php

/**
 * Part of Omega - Database Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Database\Adapter;

use Pdo;
use PDOException;
use Omega\Database\Exception\AdapterException;
use Omega\Database\Exception\ConnectionException;
use Omega\Database\Migration\AbstractMigration;
use Omega\Database\QueryBuilder\AbstractQueryBuilder;

use function in_array;
use function extension_loaded;

/**
 * Abstract database adapter class.
 *
 * The `AbstractDatabaseAdapter` is designed to provide a basic abstraction for database
 * connection and management. This class is declared as abstract and offers a basic implementation
 * of several methods defined in the `DatabaseAdapterInterface` interface.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Adapter
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
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
     * @return void
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
     * @throws AdapterException if pdo extension is not installed or not enabled.
     */
    public function pdo(): Pdo
    {
        return $this->pdo;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function checkIfDatabaseExists(
        string $host,
        string $port,
        string $username,
        string $password
    ): void;

    /**
     * {@inheritdoc}
     */
    public function hasTable(string $name): bool
    {
        $tables = $this->getTables();

        return in_array($name, $tables);
    }

    /**
     * {@inheritdoc}
     */
    abstract public function query(): AbstractQueryBuilder;

    /**
     * {@inheritdoc}
     */
    abstract public function createTable(string $table): AbstractMigration;

    /**
     * {@inheritdoc}
     */
    abstract public function alterTable(string $table): AbstractMigration;

    /**
     * {@inheritdoc}
     */
    abstract public function getTables(): array;

    /**
     * {@inheritdoc}
     */
    abstract public function dropTables(): int|bool;
}
