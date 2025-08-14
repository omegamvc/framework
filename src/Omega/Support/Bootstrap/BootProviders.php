<?php

declare(strict_types=1);

namespace Omega\Support\Bootstrap;

use Omega\Application\Application;

class BootProviders
{
    public function bootstrap(Application $app): void
    {
        $app->bootProvider();
    }
}
