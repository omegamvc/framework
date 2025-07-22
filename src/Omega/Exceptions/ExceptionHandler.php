<?php

/**
 * Part of Omega - Exception Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Exceptions;

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Container\Container;
use Omega\Http\Exceptions\HttpException;
use Omega\Http\Exceptions\HttpResponse;
use Omega\Http\Request;
use Omega\Http\Response;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;
use Throwable;

use function array_map;
use function array_merge;

/**
 * Handles exceptions and determines how to render or report them.
 *
 * This class is responsible for rendering exceptions as HTTP responses,
 * reporting them (e.g., for logging), and resolving appropriate views for
 * different exception types such as JSON or HTTP errors.
 *
 * @category  Omega
 * @package   Exceptions
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class ExceptionHandler
{
    /** @var Container The application container instance. */
    protected Container $app;

    /**
     * List of exception types that should not be reported by the application.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected array $dontReport = [];

    /** @var array<int, class-string<Throwable>> Internal exception types that should not be reported (framework-level). */
    protected array $dontReportInternal = [
        HttpResponse::class,
        HttpException::class,
    ];

    /**
     * Create a new exception handler instance.
     *
     * @param Container $application The application container.
     * @return void
     */
    public function __construct(Container $application)
    {
        $this->app = $application;
    }

    /**
     * Render an exception into an HTTP response.
     *
     * This method handles JSON exceptions, HTTP exceptions, and default
     * exceptions depending on the debug or production environment.
     *
     * @param Request   $request The current HTTP request
     * @param Throwable $th      The exception to render
     * @return Response          The HTTP response
     * @throws Throwable
     */
    public function render(Request $request, Throwable $th): Response
    {
        if ($request->isJson()) {
            return $this->handleJsonResponse($th);
        }

        if ($th instanceof HttpResponse) {
            return $th->getResponse();
        }

        if ($th instanceof HttpException) {
            return $this->handleHttpException($th);
        }

        if (false === $this->isDebug()) {
            return $this->handleResponse($th);
        }

        throw $th;
    }

    /**
     * Report the exception (for logging or error tracking).
     *
     * @param Throwable $th The exception to report
     * @return void
     */
    public function report(Throwable $th): void
    {
        if ($this->dontReport($th)) {
            return;
        }
    }

    /**
     * Determine if the exception should not be reported.
     *
     * @param Throwable $th The exception to check
     * @return bool         True if exception is in the do-not-report list
     */
    protected function dontReport(Throwable $th): bool
    {
        foreach (array_merge($this->dontReport, $this->dontReportInternal) as $report) {
            if ($th instanceof $report) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle a JSON exception and return a formatted JSON response.
     *
     * Includes detailed debug info when in debug mode.
     *
     * @param Throwable $th The exception to handle
     * @return Response     JSON response
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function handleJsonResponse(Throwable $th): Response
    {
        $response = new Response([
            'code'     => 500,
            'messages' => [
                'message'   => 'Internal Server Error',
            ]], 500);

        if ($th instanceof HttpException) {
            $response->setResponseCode($th->getStatusCode());
            $response->headers->add($th->getHeaders());
        }

        if ($this->isDebug()) {
            return $response->json([
                'code'     => $response->getStatusCode(),
                'messages' => [
                    'message'   => $th->getMessage(),
                    'exception' => $th::class,
                    'file'      => $th->getFile(),
                    'line'      => $th->getLine(),
                ],
            ]);
        }

        return $response->json();
    }

    /**
     * Handle a generic exception and return an appropriate response.
     *
     * If in production, returns a default HTTP 500 error page.
     * If in debug, includes the exception message directly.
     *
     * @param Throwable $th The exception to handle
     * @return Response     The HTTP response
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function handleResponse(Throwable $th): Response
    {
        return $this->isProduction()
            ? $this->handleHttpException(new HttpException(500, 'Internal Server Error'))
            : new Response($th->getMessage(), 500);
    }

    /**
     * Handle an HTTP exception and return the corresponding view response.
     *
     * @param HttpException $e The HTTP exception
     * @return Response        The rendered HTTP error response
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function handleHttpException(HttpException $e): Response
    {
        $templator = $this->registerViewPath();
        $code      = $templator->viewExist((string) $e->getStatusCode())
            ? $e->getStatusCode()
            : 500;

        $this->app->set('view.instance', fn () => $templator);

        $response = view((string) $code);
        $response->setResponseCode($code);
        $response->headers->add($e->getHeaders());

        return $response;
    }

    /**
     * Register the default view paths for error views and return a templator instance.
     *
     * @return Templator The view templator instance
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function registerViewPath(): Templator
    {
        $viewPaths   = array_map(fn ($path): string => $path . 'pages/', $this->app->get('paths.view'));
        $viewPaths[] = $this->app->get('path.view');
        /** @var TemplatorFinder $finder */
        $finder = $this->app->make(TemplatorFinder::class);
        $finder->setPaths($viewPaths);

        /** @var Templator $view */
        $view = $this->app->make('view.instance');
        $view->setFinder($finder);

        return $view;
    }

    /**
     * Check if the application is in debug mode.
     *
     * @return bool True if debug mode is enabled
     * @throws DependencyException
     * @throws NotFoundException
     */
    private function isDebug(): bool
    {
        return $this->app->get('app.debug');
    }

    /**
     * Check if the application is running in production mode.
     *
     * @return bool True if environment is production
     * @throws DependencyException
     * @throws NotFoundException
     */
    private function isProduction(): bool
    {
        return $this->app->get('environment') === 'prod';
    }
}
