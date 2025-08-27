<?php

declare(strict_types=1);

namespace Omega\Console;

use Omega\Application\Application;
use Omega\Container\Invoker\Exception\InvocationException;
use Omega\Container\Invoker\Exception\NotCallableException;
use Omega\Container\Invoker\Exception\NotEnoughParametersException;
use Whoops\Handler\Handler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

class ConsoleError extends Console
{
    /** @var Run */
    private Run $run;

    /** @var Handler */
    private Handler $handler;

    /**
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->app->bootedCallback(function () {
            /* @var PlainTextHandler $handler */
            $this->handler = $this->app->make('error.PlainTextHandler');

            /* @var Run $run */
            $this->run = $this->app->make('error.handle');
            $this->run
              ->pushHandler($this->handler)
              ->register();
        });
    }
}
