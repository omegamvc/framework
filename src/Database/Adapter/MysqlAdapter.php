<?php

/**
 * Part of Omega CMS - Database Package.
 *
 * @see       https://omegacms.github.io
 *
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
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
use function array_map;
use function extension_loaded;
use Omega\Database\Exception\AdapterException;
use Omega\Database\Exception\ConnectionException;
use Omega\Database\Migration\MysqlMigration;
use Omega\Database\QueryBuilder\MysqlQueryBuilder;
use Pdo;

/**
 * MySQL adapter class.
 *
 * The `MysqlDatabaseAdapter` class is an implementation of the abstract `AbstractDatabaseAdapter`
 * and is specifically tailored for MySQL database connections. This adapter provides mysql-specific
 * database management features while inheriting the common database functionality defined in the parent
 * class.
 *
 * @category    Omega
 * @package     Database
 * @subpackage  Adapter
 *
 * @see        https://omegacms.github.io
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
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

        // Assicuriamoci che il database esista
        $this->checkIfDatabaseExists($host, (string)$port, $username, $password);

        // Ora, procedi con la connessione al database selezionato
        $dsn = "mysql:host={$host};port={$port};dbname={$database}";

        parent::__construct($dsn, $username, $password);
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
     *
     * @return MysqlQueryBuilder An instance of the AbstractQueryBuilder class for constructing SQL queries.
     */
    public function query(): MysqlQueryBuilder
    {
        return new MysqlQueryBuilder($this);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $table The name of the table to create.
     *
     * @return MysqlMigration Returns an instance of the AbstractMigration class for managing table creation.
     */
    public function createTable(string $table): MysqlMigration
    {
        return new MysqlMigration($this, $table, 'create');
    }

    /**
     * {@inheritdoc}
     *
     * @param string $table The name of the table to modify.
     *
     * @return MysqlMigration Returns an instance of the AbstractMigration class for managing table alterations.
     */
    public function alterTable(string $table): MysqlMigration
    {
        return new MysqlMigration($this, $table, 'alter');
    }

    /**
     * {@inheritdoc}
     *
     * @return string[] Return a list of table names on this connection.
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
     *
     * @return int Returns 1 if all tables are successfully dropped, or false if any issues occur during the process.
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
