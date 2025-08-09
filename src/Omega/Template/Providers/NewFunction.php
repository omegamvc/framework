<?php

declare(strict_types=1);

namespace Omega\Template\Providers;

use Omega\Template\Method;

class NewFunction
{
    public static function name(string $name): Method
    {
        return new Method($name);
    }
}
