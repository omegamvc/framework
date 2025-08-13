<?php

declare(strict_types=1);

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Collection\CollectionImmutable;
use Omega\Http\RedirectResponse;
use Omega\Http\Response;
use Omega\Integrate\Application;
use Omega\Integrate\Exceptions\ApplicationNotAvailableException;
use Omega\Support\Vite;
use Omega\Router\Router;

if (!function_exists('app_path')) {
    /**
     * Get full application path, base on config file.
     *
     * @param string $folder_name Special path name.
     * @return string Application path folder.
     */
    function app_path(string $folder_name): string
    {
        $path = app()->appPath();

        return $path . DIRECTORY_SEPARATOR . $folder_name;
    }
}

if (!function_exists('model_path')) {
    /**
     * Get application model path, base on config file.
     *
     * @param string $suffix_path Add string end of path.
     * @return string Model path folder.
     */
    function model_path(string $suffix_path = ''): string
    {
        return app()->modelPath() . $suffix_path;
    }
}

if (!function_exists('view_path')) {
    /**
     * Get application base view path, use for get located view framework.
     * Remember since 0.32 view path is not single string (array of string).
     * This also include in `view_paths()`.
     *
     * @param string $suffix_path Add string end of path.
     * @return string View path folder.
     */
    function view_path(string $suffix_path = ''): string
    {
        return app()->viewPath() . $suffix_path;
    }
}

if (!function_exists('view_paths')) {
    /**
     * Get application view paths, base on config file.
     *
     * @return string[] View path folder.
     */
    function view_paths(): array
    {
        return app()->view_paths();
    }
}

if (!function_exists('controllers_path')) {
    /**
     * Get application controllers path, base on config file.
     *
     * @param string $suffix_path Add string end of path.
     * @return string Controller path folder.
     */
    function controllers_path(string $suffix_path = ''): string
    {
        return app()->controllerPath() . $suffix_path;
    }
}

if (!function_exists('services_path')) {
    /**
     * Get application services path, base on config file.
     *
     * @param string $suffix_path Add string end of path.
     * @return string Service path folder.
     */
    function services_path(string $suffix_path = ''): string
    {
        return app()->servicesPath() . $suffix_path;
    }
}

if (!function_exists('component_path')) {
    /**
     * Get application component path, base on config file.
     *
     * @param string $suffix_path Add string end of path.
     * @return string Component path folder.
     */
    function component_path(string $suffix_path = ''): string
    {
        return app()->componentPath() . $suffix_path;
    }
}

if (!function_exists('commands_path')) {
    /**
     * Get application commands path, base on config file.
     *
     * @param string $suffix_path Add string end of path.
     * @return string Command path folder.
     */
    function commands_path(string $suffix_path = ''): string
    {
        return app()->commandPath() . $suffix_path;
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get application storage path, base on config file.
     *
     * @param string $suffix_path Add string end of path.
     * @return string storage path folder.
     */
    function storage_path(string $suffix_path = ''): string
    {
        return app()->storagePath() . $suffix_path;
    }
}

if (!function_exists('cache_path')) {
    /**
     * Get application cache path, base on config file.
     *
     * @param string $suffix_path Add string end of path.
     * @return string Cache path folder.
     */
    function cache_path(string $suffix_path = ''): string
    {
        return app()->cachePath() . $suffix_path;
    }
}

if (!function_exists('compiled_view_path')) {
    /**
     * Get application compiled path., base on config file.
     */
    function compiled_view_path(): string
    {
        return app()->compiledViewPath();
    }
}

if (!function_exists('config_path')) {
    /**
     * Get application config path, base on config file.
     *
     * @param string $suffix_path Add string end of path.
     * @return string Config path folder.
     */
    function config_path(string $suffix_path = ''): string
    {
        return app()->configPath() . $suffix_path;
    }
}

if (!function_exists('middleware_path')) {
    /**
     * Get application middleware path, base on config file.
     *
     * @param string $suffix_path Add string end of path.
     * @return string Middleware path folder.
     */
    function middleware_path(string $suffix_path = ''): string
    {
        return app()->middlewarePath() . $suffix_path;
    }
}

if (!function_exists('provider_path')) {
    /**
     * Get application provider path, base on config file.
     *
     * @param string $suffix_path Add string end of path.
     * @return string Provider path folder.
     */
    function provider_path(string $suffix_path = ''): string
    {
        return app()->providerPath() . $suffix_path;
    }
}

if (!function_exists('migration_path')) {
    /**
     * Get application migration path, base on config file.
     *
     * @param string $suffix_path Add string end of path.
     * @return string Migration path folder.
     */
    function migration_path(string $suffix_path = ''): string
    {
        return app()->migrationPath() . $suffix_path;
    }
}

if (!function_exists('seeder_path')) {
    /**
     * Get application seeder path, base on config file.
     *
     * @param string $suffix_path Add string end of path.
     * @return string Seeder path folder.
     */
    function seeder_path(string $suffix_path = ''): string
    {
        return app()->seederPath() . $suffix_path;
    }
}

if (!function_exists('base_path')) {
    /**
     * Get base path.
     *
     * @param string $insert_path Insert string in end of path.
     * @return string Base path folder.
     */
    function base_path(string $insert_path = ''): string
    {
        return app()->basePath() . $insert_path;
    }
}

if (!function_exists('app_env')) {
    /**
     * Check application environment mode.
     *
     * @return string Application environment mode.
     */
    function app_env(): string
    {
        return app()->environment();
    }
}

if (!function_exists('is_production')) {
    /**
     * Check application production mode.
     *
     * @return bool True if in production mode.
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
        $app = Application::getIntance();
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
     */
    function vite(string ...$entry_points): array|string
    {
        /** @var Vite $vite */
        $vite = app()->get('vite.gets');

        return $vite(...$entry_points);
    }
}

if (!function_exists('redirect_route')) {
    /**
     * Redirect to another route.
     *
     * @param string   $route_name The name of the route.
     * @param string[] $parameter  Dynamic parameter to fill with url expression.
     * @return RedirectResponse
     * @throws Exception
     */
    function redirect_route(string $route_name, array $parameter = []): RedirectResponse
    {
        $route      = Router::redirect($route_name);
        $valueIndex = 0;
        $url        = preg_replace_callback(
            "/\(:\w+\)/",
            function ($matches) use ($parameter, &$valueIndex) {
                if (!array_key_exists($matches[0], Router::$patterns)) {
                    throw new Exception('parameter not matches with any pattern.');
                }

                if ($valueIndex < count($parameter)) {
                    $value = $parameter[$valueIndex];
                    $valueIndex++;

                    return $value;
                }

                return '';
            },
            $route['uri']
        );

        return new RedirectResponse($url);
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to Url.
     *
     * @param string $url
     * @return RedirectResponse
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
