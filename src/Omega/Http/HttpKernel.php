<?php

declare(strict_types=1);

namespace Omega\Http;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Omega\Application\Application;
use Omega\Support\Bootstrap\BootProviders;
use Omega\Support\Bootstrap\ConfigProviders;
use Omega\Support\Bootstrap\HandleExceptions;
use Omega\Support\Bootstrap\RegisterFacades;
use Omega\Support\Bootstrap\RegisterProviders;
use Omega\Exceptions\ExceptionHandler;
use Omega\Middleware\MaintenanceMiddleware;
use Omega\Router\Router;
use Throwable;
use function array_merge;
use function array_reduce;
use function is_array;
use function is_string;
use function method_exists;

class HttpKernel
{
    /**
     * Application Container.
     */
    protected Application $app;

    /** @var array<int, class-string> Global middleware */
    protected array $middleware = [
        MaintenanceMiddleware::class,
    ];

    /** @var array<int, class-string> Middleware has register */
    protected array $middlewareUsed = [];

    /** @var array<int, class-string> Application bootstrap register. */
    protected array $bootstrappers = [
        ConfigProviders::class,
        HandleExceptions::class,
        RegisterFacades::class,
        RegisterProviders::class,
        BootProviders::class,
    ];

    /**
     * Set instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle http request.
     *
     * @param Request $request Incoming request
     * @return Response Response handle
     */
    /**
     * @param Request $request
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Throwable
     */
    public function handle(Request $request): Response
    {
        $this->app->set('request', $request);

        try {
            $this->bootstrap();

            $dispatcher = $this->dispatcher($request);

            $pipeline = array_reduce(
                array_merge($this->middleware, $dispatcher['middleware']),
                fn ($next, $middleware) => fn ($req) => $this->app->call([$middleware, 'handle'], ['request' => $req, 'next' => $next]),
                fn ()                   => $this->responseType($dispatcher['callable'], $dispatcher['parameters'])
            );

            $response = $pipeline($request);
        } catch (Throwable $th) {
            $handler = $this->app->get(ExceptionHandler::class);

            $handler->report($th);
            $response = $handler->render($request, $th);
        }

        return $response;
    }

    /**
     * Register bootstrap application.
     */
    public function bootstrap(): void
    {
        $this->app->bootstrapWith($this->bootstrappers);
    }

    /**
     * Terminate Request and Response.
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function terminate(Request $request, Response $response): void
    {
        $middleware = $this->dispatcherMiddleware($request) ?? [];
        foreach (array_merge($this->middleware, $middleware) as $middleware) {
            if (method_exists($middleware, 'terminate')) {
                $this->app->call([$middleware, 'terminate'], ['request' => $request, 'response' => $response]);
            }
        }

        $this->app->terminate();
    }

    /**
     * @param callable|array|string $callable   function to call
     * @param array $parameters parameters to use
     * @return Response
     * @throws Exception
     */
    private function responseType(callable|array|string $callable, array $parameters): Response
    {
        $content = $this->app->call($callable, $parameters);
        if ($content instanceof Response) {
            return $content;
        }

        if (is_string($content)) {
            return new Response($content);
        }

        if (is_array($content)) {
            return new Response($content);
        }

        throw new Exception('Content must return as response|string|array');
    }

    /**
     * @return array<string, mixed>
     */
    protected function dispatcher(Request $request): array
    {
        return ['callable' => new Response(), 'parameters' => [], 'middleware' => []];
    }

    /**
     * Dispatch to get request middleware.
     *
     * @param Request $request
     * @return array<int, class-string>|null
     */
    protected function dispatcherMiddleware(Request $request): ?array
    {
        return Router::getCurrent()['middleware'] ?? [];
    }
}
