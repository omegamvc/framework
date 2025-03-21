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
use Omega\Database\Exception\AdapterException;
use Omega\Database\Exception\ConnectionException;
use Omega\Database\Migration\SqliteMigration;
use Omega\Database\QueryBuilder\SqliteQueryBuilder;

use function array_map;
use function array_shift;
use function extension_loaded;

/**
 * Sqlite adapter class.
 *
 * The `SqliteDatabaseAdapter` class is an implementation of the abstract `AbstractDatabaseAdapter`
 * and is specifically tailored for SQLite database connections. This adapter provides SQLite-specific
 * database management features while inheriting the common database functionality defined in the parent
 * class.
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
class SqliteAdapter extends AbstractDatabaseAdapter
{
    /**
     * Sqlite class constructor.
     *
     * @param array{path: string} $config Holds an array of configuration params.
     * @return void
     * @throws AdapterException if sqlite3 extension is not installed or not enabled.
     */
    public function __construct(array $config)
    {
        if (! extension_loaded('sqlite3')) {
            throw new AdapterException(
                'The Sqlite3 extension is not enabled. Please make sure to install or enable the Sqlite3
                            extension to use database functionality.'
            );
        }

        [ 'path' => $path ] = $config;

        if (empty($path)) {
            throw new ConnectionException(
                'Connection incorrectly configured'
            );
        }

        $dsn = "sqlite:{$path}";

        parent::__construct($dsn);
    }

    /**
     * {@inheritdoc}
     */
    public function checkIfDatabaseExists(
        string $host,
        string $port,
        string $username,
        string $password
    ): void {
        // Not necessary in SQLite3
    }

    /**
     * {@inheritdoc}
     */
    public function createDatabase(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabaseName(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     *
     * @return SqliteQueryBuilder An instance of the AbstractQueryBuilder class for constructing SQL queries.
     */
    public function query(): SqliteQueryBuilder
    {
        return new SqliteQueryBuilder($this);
    }

    /**
     * {@inheritdoc}
     * @return SqliteMigration Returns an instance of the AbstractMigration class for managing table creation.
     */
    public function createTable(string $table): SqliteMigration
    {
        return new SqliteMigration($this, $table, 'create');
    }

    /**
     * {@inheritdoc}
     * @return SqliteMigration Return an instance of MysqlMigration.
     */
    public function alterTable(string $table): SqliteMigration
    {
        return new SqliteMigration($this, $table, 'alter');
    }

    /**
     * {@inheritdoc}
     *
     * @return string[] Returns an array of table names available on this connection.
     */
    public function getTables(): array
    {
        $statement = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table'");
        $statement->execute();

        $results = $statement->fetchAll(PDO::FETCH_NUM);

        return array_map(fn($result) => $result[0], $results);
    }

    /**
     * {@inheritdoc}
     *
     * @return int Returns 1 if all tables are successfully dropped, or false if any issues occur during the process.
     */
    public function dropTables(): int|bool
    {
        $statement = $this->pdo->prepare("
            SELECT name FROM sqlite_master WHERE type='table' AND name !='sqlite_sequence'
        ");
        $statement->execute();
        $tables = $statement->fetchAll(PDO::FETCH_COLUMN);

        array_shift($tables);

        foreach ($tables as $table) {
            $dropStatement = $this->pdo->prepare("DROP TABLE IF EXISTS `$table`");
            $dropStatement->execute();
        }

        return 1;
    }
}
