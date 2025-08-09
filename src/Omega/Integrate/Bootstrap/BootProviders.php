<?php

declare(strict_types=1);

namespace Omega\Integrate\Bootstrap;

use Omega\Integrate\Application;

class BootProviders
{
    public function bootstrap(Application $app): void
    {
        $app->bootProvider();
    }
}
