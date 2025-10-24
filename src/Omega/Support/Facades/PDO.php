<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\Database\Connection;

/**
 * @method static Connection        getInstance()
 * @method static Connection        conn(array<string, string> $configs)
 * @method static array        configs()
 * @method static string       getDsn(array $configs)
 * @method static Connection        query(string $query)
 * @method static Connection        bind(string|int|bool|null $param, mixed $value, string|int|bool|null $type = null)
 * @method static bool         execute()
 * @method static array|false  resultset()
 * @method static mixed        single()
 * @method static int          rowCount()
 * @method static string|false lastInsertId()
 * @method static bool         transaction(callable $callable)
 * @method static bool         beginTransaction()
 * @method static bool         endTransaction()
 * @method static bool         cancelTransaction()
 * @method static void         flushLogs()
 * @method static array        getLogs()
 *
 * @see Connection
 */
final class PDO extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return Connection::class;
    }
}
