<?php

declare(strict_types=1);

use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Collection\CollectionImmutable;
use Omega\Router\RouteUrlBuilder;
use Omega\Support\Env;
use Omega\Http\RedirectResponse;
use Omega\Http\Response;
use Omega\Application\Application;
use Omega\Exceptions\ApplicationNotAvailableException;
use Omega\Support\Vite;
use Omega\Router\Router;

if (!function_exists('app_env')) {
    /**
     * Check application environment mode.
     *
     * @return string Application environment mode.
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    function app_env(): string
    {
        return app()->getEnvironment();
    }
}

if (!function_exists('is_production')) {
    /**
     * Check application production mode.
     *
     * @return bool True if in production mode.
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    function is_production(): bool
    {
        return app()->isProduction();
    }
}

if (!function_exists('is_dev')) {
    /**
     * Check application development mode.
     *
     * @return bool True if in dev mode.
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    function is_dev(): bool
    {
        return app()->isDev();
    }
}

if (!function_exists('app')) {
    /**
     * Get Application container.
     *
     * @return Application Return the current application instance.
     * @throws ApplicationNotAvailableException if the application is not started.
     */
    function app(): Application
    {
        $app = Application::getInstance();
        if (null === $app) {
            throw new ApplicationNotAvailableException();
        }

        return $app;
    }
}

if (!function_exists('config')) {
    /**
     * Get Application Configuration.
     *
     * @return CollectionImmutable<string, mixed>
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    function config(): CollectionImmutable
    {
        return new CollectionImmutable(app()->get('config'));
    }
}

if (!function_exists('view')) {
    /**
     * Render with custom template engine, wrap in `Route\Controller`.
     *
     * @param string               $view_path
     * @param array<string, mixed> $data
     * @param array<string, mixed> $option
     * @return Response
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    function view(string $view_path, array $data = [], array $option = []): Response
    {
        $view        = app()->get('view.response');
        $status_code = $option['status'] ?? 200;
        $headers     = $option['header'] ?? [];

        return $view($view_path, $data)
            ->setResponseCode($status_code)
            ->setHeaders($headers);
    }
}

if (!function_exists('vite')) {
    /**
     * Get resource using entry point(s).
     *
     * @param string ...$entry_points
     * @return array<string, string>|string
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Exception
     */
    function vite(string ...$entry_points): array|string
    {
        /** @var Vite $vite */
        $vite = app()->get('vite.gets');

        $resource = $vite->gets($entry_points);
        $first    = array_key_first($resource);

        return 1 === count($resource) ? $resource[$first] : $resource;
    }
}

if (!function_exists('redirect_route')) {
    /**
     * Redirect to another route.
     *
     * @param string   $route_name The name of the route.
     * @param array<string|int, string|int|bool> $parameter Dynamic parameter to fill with url expression
     * @return RedirectResponse
     * @throws Exception
     */
    function redirect_route(string $route_name, array $parameter = []): RedirectResponse
    {
        $route   = Router::redirect($route_name);
        $builder = new RouteUrlBuilder(Router::$patterns);

        return new RedirectResponse($builder->buildUrl($route, $parameter));
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to Url.
     *
     * @param string $url
     * @return RedirectResponse
     * @throws Exception
     */
    function redirect(string $url): RedirectResponse
    {
        return new RedirectResponse($url);
    }
}

if (!function_exists('abort')) {
    /**
     * Abort application to http exception.
     *
     * @param int                   $code
     * @param string                $message
     * @param array<string, string> $headers
     * @return void
     */
    function abort(int $code, string $message = '', array $headers = []): void
    {
        app()->abort($code, $message, $headers);
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('set_path')) {
    function set_path(string $key): string
    {
        if ($key === '') {
            throw new InvalidArgumentException('The path key cannot be an empty string.');
        }

        return DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $key) . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('get_path')) {
    /**
     * Get application config path, base on config file.
     *
     * @param string|array $id
     * @param string $suffix_path Add string end of path.
     * @return string|array Config path folder or an array of config path folder.
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    function get_path(string|array $id, string $suffix_path = ''): string|array
    {
        $value = app()->get($id);

        if (is_array($value)) {
            return array_map(fn ($v) => $v . $suffix_path, $value);
        }

        return $value . $suffix_path;
    }
}

if (!function_exists('path')) {
    function path(string $binding): string
    {
        $relativePath = str_replace('.', DIRECTORY_SEPARATOR, $binding);

        if (!str_ends_with($relativePath, DIRECTORY_SEPARATOR)) {
            $relativePath .= DIRECTORY_SEPARATOR;
        }

        return $relativePath;
    }
}
