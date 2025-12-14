<?php

declare(strict_types=1);

namespace Omega\Middleware;

use Closure;
use Exception;
use Omega\Container\Exceptions\BindingResolutionException;
use Omega\Container\Exceptions\CircularAliasException;
use Omega\Container\Exceptions\EntryNotFoundException;
use Omega\Http\Request;
use Omega\Http\Response;
use Omega\Application\Application;
use Omega\Http\Exceptions\HttpException;
use ReflectionException;

class MaintenanceMiddleware
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws Exception
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->app->isDownMaintenanceMode()) {
            $data = $this->app->getDownData();

            if (isset($data['redirect'])) {
                return redirect($data['redirect']);
            }

            if (isset($data['template'])) {
                $header = isset($data['retry']) ? ['Retry-After' => $data['retry']] : [];

                return new Response($data['template'], $data['status'] ?? 503, $header);
            }

            throw new HttpException($data['status'] ?? 503, 'Service Unavailable');
        }

        return $next($request);
    }
}
