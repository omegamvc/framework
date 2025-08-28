<?php

/**
 * Part of Omega - Console Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Console;

use Omega\Application\Application;
use Omega\Container\Invoker\Exception\InvocationException;
use Omega\Container\Invoker\Exception\NotCallableException;
use Omega\Container\Invoker\Exception\NotEnoughParametersException;
use Whoops\Handler\Handler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

/**
 * ConsoleError integrates Whoops error handling into the console application.
 *
 * @category  Omega
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class ConsoleError extends Console
{
    /** @var Run The Whoops run instance responsible for error handling. */
    private Run $run;

    /** @var Handler The Whoops plain text handler for rendering errors. */
    private Handler $handler;

    /**
     * Create a new ConsoleError instance and register Whoops error handling.
     *
     * @param Application $app The application container instance.
     * @return void
     * @throws InvocationException If a callable cannot be invoked.
     * @throws NotCallableException If a handler is not callable.
     * @throws NotEnoughParametersException If insufficient parameters are provided to a callable.
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
