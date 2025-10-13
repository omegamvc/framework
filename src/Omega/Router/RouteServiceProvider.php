<?php

declare(strict_types=1);

namespace Omega\Router;

use App\Middlewares\AppMiddleware;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Container\Provider\AbstractServiceProvider;

class RouteServiceProvider extends AbstractServiceProvider
{
    /**
     * @throws InvalidDefinitionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function boot(): void
    {
        if (file_exists($cache = $this->app->getApplicationCachePath() . 'route.php')) {
            $routes = (array) require $cache;
            foreach ($routes as $route) {
                Router::addRoutes($route);
            }
        } else {
            Router::middleware([
                // middleware
                AppMiddleware::class,
            ])->group(
                fn () => [
                    require_once get_path('path.base', '/routes/web.php'),
                    require_once get_path('path.base', '/routes/api.php'),
                ]
            );
        }

        require_once get_path('path.base', '/routes/schedule.php');
    }
}
