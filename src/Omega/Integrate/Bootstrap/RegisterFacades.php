<?php

declare(strict_types=1);

namespace Omega\Integrate\Bootstrap;

use Omega\Integrate\Application;
use Omega\Support\Facades\AbstractFacade;

class RegisterFacades
{
    public function bootstrap(Application $app): void
    {
        AbstractFacade::setFacadeBase($app);
    }
}
