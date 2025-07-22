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

namespace Omega\Http\Middleware;

use Closure;
use Omega\Http\Exceptions\HttpException;
use Omega\Http\Request;
use Omega\Http\Response;
use Omega\Application\Application;

/**
 * MaintenanceMiddleware
 *
 * This middleware checks whether the application is in maintenance mode and handles the request accordingly.
 * If maintenance mode is enabled, it can:
 * - Redirect the user to a specific URL.
 * - Render a custom maintenance template with optional headers.
 * - Throw a generic HTTP 503 (Service Unavailable) exception if no specific instructions are given.
 *
 * If the application is not in maintenance mode, it passes the request to the next middleware.
 *
 * This is particularly useful for gracefully handling downtime or deployments without interrupting the user experience.
 *
 * @category   Omega
 * @package    Http
 * @subpackage Middleware
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class MaintenanceMiddleware
{
    /**
     * Create a new instance of the MaintenanceMiddleware.
     *
     * @param Application $app The core application instance used to check maintenance
     *                         state and retrieve configuration.
     * @return void
     */
    public function __construct(protected Application $app)
    {
    }

    /**
     * Handle an incoming HTTP request.
     *
     * If the application is in maintenance mode, it will either:
     * - Redirect the request to a predefined URL.
     * - Return a custom HTML response with an optional Retry-After header.
     * - Throw a Service Unavailable HTTP exception.
     *
     * If the application is not in maintenance mode, the request is passed to the next middleware.
     *
     * @param Request $request The current HTTP request instance.
     * @param Closure $next The next middleware in the pipeline.
     * @return Response The HTTP response returned to the client.
     * @throws HttpException If no redirect or template is provided during maintenance mode.
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
