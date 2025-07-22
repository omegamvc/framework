<?php

declare(strict_types=1);

namespace Omega\Template\Providers;

use Omega\Template\Property;

class NewProperty
{
    public static function name(string $name): Property
    {
        return new Property($name);
    }
}
