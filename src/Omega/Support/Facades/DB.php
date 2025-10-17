<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\Database\DatabaseManager;
use Omega\Database\ConnectionInterface;
use Omega\Database\MyQuery\InnerQuery;
use Omega\Database\MyQuery\Table;

/**
 * @method static void                clearConnections()
 * @method static ConnectionInterface connection(string $name)
 * @method static DatabaseManager     setDefaultConnection(ConnectionInterface $connection)
 * @method static DatabaseManager     query(string $query)
 * @method static DatabaseManager     bind(string|int|bool|null $param, mixed $value, string|int|bool|null $type = null)
 * @method static bool                execute()
 * @method static mixed[]|false       resultset()
 * @method static mixed               single()
 * @method static int                 rowCount()
 * @method static bool                transaction(callable $callable)
 * @method static bool                beginTransaction()
 * @method static bool                endTransaction()
 * @method static bool                cancelTransaction()
 * @method static string|false        lastInsertId()
 * @method static void                flushLogs()
 * @method static array               getLogs()
 *
 * @see DatabaseManager
 */
final class DB extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return DatabaseManager::class;
    }

    /**
     * Create builder and set table name.
     */
    public static function table(string|InnerQuery $table_name): Table
    {
        return new Table($table_name, PDO::instance());
    }
}
