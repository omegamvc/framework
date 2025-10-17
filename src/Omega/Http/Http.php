<?php

declare(strict_types=1);

namespace Omega\Http;

use Closure;
use InvalidArgumentException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Exception;
use Omega\Application\Application;
use Omega\Container\Invoker\Exception\InvocationException;
use Omega\Container\Invoker\Exception\NotCallableException;
use Omega\Container\Invoker\Exception\NotEnoughParametersException;
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

class Http
{
    /**
     * Application Container.
     */
    protected Application $app;

    /** @var array<int, class-string|string> Global middleware */
    protected array $middleware = [
        MaintenanceMiddleware::class,
    ];

    /** @var array<int, class-string|string> Middleware has register */
    protected array $middlewareUsed = [];

    /** @var array<int, class-string|string> Application bootstrap register. */
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
            $request->with($dispatcher['parameters']);

            $middleware = array_merge($this->middleware, $dispatcher['middleware']);
            $pipeline   = $this->middlewarePipeline($middleware, $dispatcher);
            $response   = $pipeline($request);
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
     * @return array<int, class-string|string>|null
     */
    protected function dispatcherMiddleware(Request $request): ?array
    {
        return Router::getCurrent()['middleware'] ?? [];
    }

    /**
     * @param array<int, class-string|string|object>                      $middleware
     * @param array{callable: callable, parameters: array<string, mixed>} $dispatcher
     * @return Closure(Request): Response
     * @throws Exception
     */
    protected function middlewarePipeline(array $middleware, array $dispatcher): Closure
    {
        return array_reduce(
            array_reverse($middleware),
            fn ($next, $middleware): Closure => fn (Request $request): Response => $this->executeMiddleware(
                $middleware,
                $request,
                $next
            ),
            fn (): Response                  => $this->responseType($dispatcher['callable'], $dispatcher['parameters'])
        );
    }

    /**
     * Execute a middleware.
     *
     * @param class-string|string $middleware Middleware instance, class name, or callable
     * @param Request $request
     * @param callable $next
     * @return Response
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     */
    protected function executeMiddleware(string $middleware, Request $request, callable $next): Response
    {
        if (false === method_exists($middleware, 'handle')) {
            throw new InvalidArgumentException('Middleware must be a class with handle method');
        }

        return $this->app->call(
            [$middleware, 'handle'],
            ['request' => $request, 'next' => $next]
        );
    }
}
