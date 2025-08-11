<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

/**
 * @method static \Omega\Database\MyPDO instance()
 */
final class PDO extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return \Omega\Database\MyPDO::class;
    }
}
