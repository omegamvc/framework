<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

/**
 * @method static \Omega\Database\MyPDO instance()
 */
final class PDO extends Facade
{
    protected static function getAccessor()
    {
        return \Omega\Database\MyPDO::class;
    }
}
