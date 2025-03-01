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
use Omega\Database\Migration\MysqlMigration;
use Omega\Database\QueryBuilder\MysqlQueryBuilder;

use function array_map;
use function extension_loaded;

/**
 * MySQL adapter class.
 *
 * The `MysqlDatabaseAdapter` class is an implementation of the abstract `AbstractDatabaseAdapter`
 * and is specifically tailored for MySQL database connections. This adapter provides mysql-specific
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
class MysqlAdapter extends AbstractDatabaseAdapter
{
    /**
     * Database name.
     *
     * @var string Holds the database name.
     */
    private string $database;

    /**
     * MySQL class constructor.
     *
     * @param array{
     *     host: string,
     *     port: string,
     *     database: string,
     *     username: string,
     *     password: string
     * } $config Holds an array of configuration parameters.
     * @return void
     * @throws AdapterException if mysql extension is not installed or not enabled.
     */
    public function __construct(array $config)
    {
        if (! extension_loaded('mysqli')) {
            throw new AdapterException(
                'The MySQL extension is not enabled. Please make sure to install or enable the MySQL
                            extension to use database functionality.'
            );
        }

        [
            'host'     => $host,
            'port'     => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
        ] = $config;

        if (empty($host) || empty($database) || empty($username)) {
            throw new ConnectionException(
                'Connection incorrectly configured'
            );
        }

        $this->database = $database;

        $this->checkIfDatabaseExists($host, $port, $username, $password);
        $dsn = "mysql:host={$host};port={$port};dbname={$database}";

        parent::__construct($dsn, $username, $password);
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
        $dsn = "mysql:host={$host};port={$port}";
        $pdo = new Pdo($dsn, $username, $password);

        $statement = $pdo->prepare('SHOW DATABASES LIKE :database');
        $statement->execute([':database' => $this->database]);
        $exists = $statement->fetch();

        if (!$exists) {
            $pdo->exec("CREATE DATABASE `{$this->database}`");
        }
    }

    /**
     * {@inheritdoc}
     * @return MysqlQueryBuilder An instance of the AbstractQueryBuilder class for constructing SQL queries.
     */
    public function query(): MysqlQueryBuilder
    {
        return new MysqlQueryBuilder($this);
    }

    /**
     * {@inheritdoc}
     * @return MysqlMigration Returns an instance of the AbstractMigration class for managing table creation.
     */
    public function createTable(string $table): MysqlMigration
    {
        return new MysqlMigration($this, $table, 'create');
    }

    /**
     * {@inheritdoc}
     * @return MysqlMigration Returns an instance of the AbstractMigration class for managing table alterations.
     */
    public function alterTable(string $table): MysqlMigration
    {
        return new MysqlMigration($this, $table, 'alter');
    }

    /**
     * {@inheritdoc}
     */
    public function getTables(): array
    {
        $statement = $this->pdo->prepare('SHOW TABLES');
        $statement->execute();

        $results = $statement->fetchAll(PDO::FETCH_NUM);

        return array_map(fn($result) => $result[0], $results);
    }

    /**
     * {@inheritdoc}
     */
    public function dropTables(): int|bool
    {
        $statement = $this->pdo->prepare("
            SELECT CONCAT('DROP TABLE IF EXISTS `', table_name, '`')
            FROM information_schema.tables
            WHERE table_schema = '{$this->database}';
        ");

        $statement->execute();

        $dropTableClauses = $statement->fetchAll(PDO::FETCH_NUM);
        $dropTableClauses = array_map(fn($result) => $result[0], $dropTableClauses);

        $clauses = [
            'SET FOREIGN_KEY_CHECKS = 0',
            ...$dropTableClauses,
            'SET FOREIGN_KEY_CHECKS = 1',
        ];

        $statement = $this->pdo->prepare(join(';', $clauses) . ';');

        return $statement->execute();
    }
}
