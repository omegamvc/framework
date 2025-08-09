<?php

declare(strict_types=1);

namespace Omega\Integrate\Bootstrap;

use Omega\Integrate\Application;

class RegisterProviders
{
    public function bootstrap(Application $app): void
    {
        $app->registerProvider();
    }
}
