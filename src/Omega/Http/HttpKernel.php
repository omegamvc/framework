<?php

/**
 * Part of Omega - Http Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Http;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Omega\Application\Application;
use Omega\Http\Middleware\MaintenanceMiddleware;
use Omega\Support\Bootstrap\RegisterFacades;
use Omega\Support\Bootstrap\RegisterProviders;
use Omega\Exceptions\ExceptionHandler;
use Omega\Router\Router;
use Omega\Support\Bootstrap\BootProviders;
use Omega\Support\Bootstrap\ConfigProviders;
use Omega\Support\Bootstrap\HandleExceptions;
use Throwable;

use function array_merge;
use function array_reduce;
use function is_array;
use function is_string;
use function method_exists;

/**
 * Core HTTP Kernel responsible for handling HTTP requests and sending responses.
 *
 * This class bootstraps the application, resolves middleware pipelines,
 * dispatches the appropriate controller or callback, and handles exceptions.
 *
 * @category  Omega
 * @package   Http
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class HttpKernel
{
    /**
     * Global HTTP middleware stack.
     *
     * These middleware are applied to every request.
     *
     * @var array<int, class-string>
     */
    protected array $middleware = [
        MaintenanceMiddleware::class,
    ];

    /**
     * Tracks middleware that have been registered during the request lifecycle.
     *
     * @var array<int, class-string>
     */
    protected array $middlewareUsed = [];

    /**
     * List of service providers used to bootstrap the application.
     *
     * These providers handle configuration, exception handling,
     * facades, and other boot-time responsibilities.
     *
     * @var array<int, class-string>
     */
    protected array $providers = [
        ConfigProviders::class,
        HandleExceptions::class,
        RegisterFacades::class,
        RegisterProviders::class,
        BootProviders::class,
    ];

    /**
     * Create a new HTTP kernel instance.
     *
     * @param Application $app The application container instance.
     */
    public function __construct(protected Application $app)
    {
    }

    /**
     * Handle an incoming HTTP request and return the corresponding response.
     *
     * This method bootstraps the application, resolves middleware,
     * invokes the matched route/controller, and catches/report exceptions.
     *
     * @param Request $request The incoming HTTP request.
     * @return Response The final HTTP response.
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
     * Bootstrap the application using the configured service providers.
     *
     * This method ensures all registered providers are loaded and booted.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        $this->app->bootstrapWith($this->providers);
    }

    /**
     * Perform any final tasks after the response has been sent.
     *
     * This includes running middleware `terminate()` methods and shutting down the app.
     *
     * @param Request $request The original request.
     * @param Response $response The final response sent to the client.
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
     * Normalize controller or callback output into a Response object.
     *
     * Accepts string, array, or Response object; throws if invalid type.
     *
     * @param callable|array|string $callable The resolved route/controller.
     * @param array $parameters The parameters to pass to the callable.
     * @return Response A proper Response instance.
     * @throws Exception If the result is not a valid response type.
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
     * Resolve the route dispatcher, returning the callable, parameters, and middleware.
     *
     * @param Request $request The incoming HTTP request.
     * @return array<string, mixed> Dispatcher data including:
     *     - 'callable': The route callback/controller
     *     - 'parameters': Parameters to pass
     *     - 'middleware': Middleware to apply
     */
    protected function dispatcher(Request $request): array
    {
        return ['callable' => new Response(), 'parameters' => [], 'middleware' => []];
    }

    /**
     * Retrieve route-specific middleware for the current request.
     *
     * @param Request $request The incoming HTTP request.
     * @return array<int, class-string>|null An array of middleware class names, or null if none found.
     */
    protected function dispatcherMiddleware(Request $request): ?array
    {
        return Router::current()['middleware'] ?? [];
    }
}
