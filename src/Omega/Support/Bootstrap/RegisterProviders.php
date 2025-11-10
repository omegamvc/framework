<?php

declare(strict_types=1);

namespace Omega\Support\Bootstrap;

use Omega\Application\Application;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Container\Invoker\Exception\InvocationException;
use Omega\Container\Invoker\Exception\NotCallableException;
use Omega\Container\Invoker\Exception\NotEnoughParametersException;

class RegisterProviders
{
    /**
     * @throws InvalidDefinitionException
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotFoundException
     * @throws DependencyException
     * @throws NotEnoughParametersException
     */
    public function bootstrap(Application $app): void
    {
        $app->registerProvider();
    }
}
