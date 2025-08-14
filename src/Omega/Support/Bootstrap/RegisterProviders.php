<?php

declare(strict_types=1);

namespace Omega\Support\Bootstrap;

use Omega\Application\Application;

class RegisterProviders
{
    public function bootstrap(Application $app): void
    {
        $app->registerProvider();
    }
}
