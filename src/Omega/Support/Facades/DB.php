<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

/**
 * @method static \Omega\Database\MyQuery\Table table(string $from)
 */
final class DB extends Facade
{
    protected static function getAccessor()
    {
        return 'MyQuery';
    }
}
