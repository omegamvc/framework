<?php

declare(strict_types=1);

namespace Omega\Http;

use Omega\Application\Application;
use Omega\Container\Exceptions\BindingResolutionException;
use Omega\Container\Exceptions\EntryNotFoundException;
use Omega\Router\RouteDispatcher;
use Omega\Router\Router;
use ReflectionException;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class HttpError extends Http
{
    /**
     * @param Application $app
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->app->bootedCallback(function () {
            if ($this->app->isDebugMode() && class_exists(Run::class)) {
                /* @var PrettyPageHandler $hanlder */
                $handler = $this->app->make('error.PrettyPageHandler');
                $handler->setPageTitle('php mvc');

                /* @var Run $run */
                $run = $this->app->make('error.handle');
                $run
                    ->pushHandler($handler)
                    ->register();
            }
        });
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function dispatcher(Request $request): array
    {
        $dispatcher = new RouteDispatcher($request, Router::getRoutesRaw());

        $content = $dispatcher->run(
        // found
            fn($callable, $param) => $this->app->call($callable, $param),
            // not found
            fn($path) => view('pages/404', [
                'path'    => $path,
                'headers' => ['status' => 404],
            ]),
            // method not allowed
            fn($path, $method) => view('pages/405', [
                'path'    => $path,
                'method'  => $method,
                'headers' => ['status' => 405],
            ])
        );

        return [
            'callable'   => $content['callable'],
            'parameters' => $content['params'],
            'middleware' => $content['middleware'],
        ];
    }
}
