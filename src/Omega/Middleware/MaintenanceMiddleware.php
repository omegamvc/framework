<?php

declare(strict_types=1);

namespace Omega\Middleware;

use Omega\Http\Request;
use Omega\Http\Response;
use Omega\Application\Application;
use Omega\Http\Exceptions\HttpException;

class MaintenanceMiddleware
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle(Request $request, \Closure $next): Response
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
