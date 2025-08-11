<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\Database\MyQuery\Table;

/**
 * @method static Table table(string $from)
 */
final class DB extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'MyQuery';
    }
}
