<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\Database\Schema\Create;
use Omega\Database\Schema\Drop;
use Omega\Database\Schema\Table\Alter;
use Omega\Database\Schema\Table\Create as TableCreate;
use Omega\Database\Schema\Table\Raw;
use Omega\Database\Schema\Table\Truncate;

/**
 * @method static Create      create()
 * @method static Drop        drop()
 * @method static Truncate    refresh(string $table_name)
 * @method static TableCreate table(string $table_name, callable $blueprint)
 * @method static Alter       alter(string $table_name, callable $blueprint)
 * @method static Raw         raw(string $raw)
 *
 * @see Schema
 */
final class Schema extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'Schema';
    }
}
