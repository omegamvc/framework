<?php

declare(strict_types=1);

namespace Omega\Exceptions;

use Omega\Container\Container;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Http\Exceptions\HttpException;
use Omega\Http\Exceptions\HttpResponseException;
use Omega\Http\Request;
use Omega\Http\Response;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;
use Throwable;

use function array_map;
use function array_merge;

class ExceptionHandler
{
    protected Container $app;

    /**
     * Do not report exception list.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected array $dontReport = [];

    /**
     * Do not report exception list internal (framework).
     *
     * @var array<int, class-string<Throwable>>
     */
    protected array $dontReportInternal = [
        HttpResponseException::class,
        HttpException::class,
    ];

    public function __construct(Container $application)
    {
        $this->app = $application;
    }

    /**
     * Render exception.
     *
     * @param Request   $request
     * @param Throwable $th
     * @return Response
     * @throws Throwable
     */
    public function render(Request $request, Throwable $th): Response
    {
        if ($request->isJson()) {
            return $this->handleJsonResponse($th);
        }

        if ($th instanceof HttpResponseException) {
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
     * Report exception (usefully for logging).
     *
     * @param Throwable $th
     * @return void
     */
    public function report(Throwable $th): void
    {
        if ($this->dontReport($th)) {
            return;
        }
    }

    /**
     * Determinate if exception in list of do not report.
     *
     * @param Throwable $th
     * @return bool
     */
    protected function dontReport(Throwable $th): bool
    {
        return array_any(
            array_merge(
                $this->dontReport,
                $this->dontReportInternal
            ),
            fn($report) => $th instanceof $report
        );
    }

    /**
     * @param Throwable $th
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidDefinitionException
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
     * @param Throwable $th
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidDefinitionException
     */
    protected function handleResponse(Throwable $th): Response
    {
        return $this->isProduction()
            ? $this->handleHttpException(new HttpException(500, 'Internal Server Error'))
            : new Response($th->getMessage(), 500);
    }

    /**
     * @param HttpException $e
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidDefinitionException
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
     * Register error view path.
     *
     * @return Templator
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidDefinitionException
     */
    public function registerViewPath(): Templator
    {
        $view_paths   = array_map(fn ($path): string => $path . 'pages/', $this->app->get('paths.view'));
        $view_paths[] = $this->app->get('path.view');
        /** @var TemplatorFinder $finder */
        $finder = $this->app->make(TemplatorFinder::class);
        $finder->setPaths($view_paths);

        /** @var Templator $view */
        $view = $this->app->make('view.instance');
        $view->setFinder($finder);

        return $view;
    }

    /**
     * @return bool
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidDefinitionException
     */
    private function isDebug(): bool
    {
        return $this->app->get('app.debug');
    }

    /**
     * @return bool
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidDefinitionException
     */
    private function isProduction(): bool
    {
        return $this->app->get('environment') === 'prod';
    }
}
