<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\Database\MyPDO;
use Omega\Database\MyQuery\InnerQuery;
use Omega\Database\MyQuery\Table;

/**
 * @method static Table table(InnerQuery|string $table_name)
 * @method static Table from(InnerQuery|string $table_name, MyPDO $PDO)
 *
 * @see \Omega\Database\MyQuery
 */
final class DB extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'MyQuery';
    }
}
