<?php

declare(strict_types=1);

namespace Omega\Template\Providers;

use Omega\Template\Constant;

class NewConst
{
    public static function name(string $name): Constant
    {
        return new Constant($name);
    }
}
