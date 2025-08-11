<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\Database\MySchema\Create;
use Omega\Database\MySchema\Drop;
use Omega\Database\MySchema\Table\Alter;
use Omega\Database\MySchema\Table\Create as TableCreate;
use Omega\Database\MySchema\Table\Raw;
use Omega\Database\MySchema\Table\Truncate;

/**
 * @method static Create create()
 * @method static Drop drop()
 * @method static Truncate refresh(string $table_name)
 * @method static TableCreate table(string $table_name, callable $blueprint)
 * @method static Alter alter(string $table_name, callable $blueprint)
 * @method static Raw raw(string $raw)
 */
final class Schema extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'MySchema';
    }
}
