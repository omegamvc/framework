<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

/**
 * @method static \Omega\Database\MySchema\Create         create()
 * @method static \Omega\Database\MySchema\Drop           drop()
 * @method static \Omega\Database\MySchema\Table\Truncate refresh(string $table_name)
 * @method static \Omega\Database\MySchema\Table\Create   table(string $table_name, callable $blueprint)
 * @method static \Omega\Database\MySchema\Table\Alter    alter(string $table_name, callable $blueprint)
 * @method static \Omega\Database\MySchema\Table\Raw      raw(string $raw)
 */
final class Schema extends Facade
{
    protected static function getAccessor()
    {
        return 'MySchema';
    }
}
