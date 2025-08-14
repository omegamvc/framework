<?php

declare(strict_types=1);

namespace Omega\Support\Bootstrap;

use Omega\Application\Application;
use Omega\Support\Facades\AbstractFacade;

class RegisterFacades
{
    public function bootstrap(Application $app): void
    {
        AbstractFacade::setFacadeBase($app);
    }
}
