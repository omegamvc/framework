<?php

/**
 * Part of Omega - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Collection\CollectionImmutable;
use Omega\Http\RedirectResponse;
use Omega\Http\Response;
use Omega\Application\Application;
use Omega\Exceptions\ApplicationNotAvailableException;
use Omega\Support\Vite;
use Omega\Router\Router;

/**
 * Application Helper Functions
 *
 * This file contains a collection of global helper functions designed to
 * simplify access to various paths and services within the Omega application.
 * These functions provide convenient shortcuts to retrieve paths for
 * application components such as models, views, controllers, services,
 * configuration files, storage, cache, and more.
 *
 * Additionally, helper functions for environment detection (e.g., is_dev,
 * is_production), application instance retrieval, configuration access,
 * view rendering, redirection, and request abortion are included.
 *
 * Each function checks for existence before declaration to avoid redeclaration
 * errors and relies on the central Application container to provide the
 * underlying implementations.
 *
 * This file is intended to be included early in the application bootstrap
 * process to ensure helper availability throughout the codebase.
 *
 * @category  Omega
 * @package   Support
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

if (!function_exists('app_path')) {
    /**
     * Get the full application path based on the configuration.
     *
     * @param string $folder_name Subfolder name within the application path
     * @return string The full path to the specified application folder
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function app_path(string $folder_name = ''): string
    {
        return app()->getAppPath() . $folder_name;

//        return $path . DIRECTORY_SEPARATOR . $folder_name;
    }
}

if (!function_exists('model_path')) {
    /**
     * Get the full path to the application's models directory.
     *
     * @param string $suffix_path Optional suffix to append to the models path
     * @return string The full path to the model directory with optional suffix
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function model_path(string $suffix_path = ''): string
    {
        return app()->getModelPath() . $suffix_path;
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the full path to the application's public directory.
     *
     * @param string $suffix_path Optional suffix to append to the public path
     * @return string The full path to the public directory with optional suffix
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function public_path(string $suffix_path = ''): string
    {
        return app()->getPublicPath() . $suffix_path;
    }
}

if (!function_exists('view_path')) {
    /**
     * Get the base path for application views.
     *
     * Note: Since version 0.32, the view path can be an array of paths,
     * this function returns the primary base path.
     *
     * @param string $suffix_path Optional suffix to append to the view path
     * @return string The full path to the views directory with optional suffix
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function view_path(string $suffix_path = ''): string
    {
        return app()->getViewPath() . $suffix_path;
    }
}

if (!function_exists('view_paths')) {
    /**
     * Get all configured view paths for the application.
     *
     * @return string[] Array of all view paths configured in the application
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function view_paths(): array
    {
        return app()->getViewPaths();
    }
}

if (!function_exists('controllers_path')) {
    /**
     * Get the full path to the application's controllers directory.
     *
     * @param string $suffix_path Optional suffix to append to the controllers path
     * @return string The full path to the controllers directory with optional suffix
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function controllers_path(string $suffix_path = ''): string
    {
        return app()->getControllerPath() . $suffix_path;
    }
}

if (!function_exists('services_path')) {
    /**
     * Get the full path to the application's services directory.
     *
     * @param string $suffix_path Optional suffix to append to the services path
     * @return string The full path to the services directory with optional suffix
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function services_path(string $suffix_path = ''): string
    {
        return app()->getServicesPath() . $suffix_path;
    }
}

if (!function_exists('component_path')) {
    /**
     * Get the full path to the application's components directory.
     *
     * @param string $suffix_path Optional suffix to append to the components path
     * @return string The full path to the components directory with optional suffix
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function component_path(string $suffix_path = ''): string
    {
        return app()->getComponentPath() . $suffix_path;
    }
}

if (!function_exists('commands_path')) {
    /**
     * Get the full path to the application's commands directory.
     *
     * @param string $suffix_path Optional suffix to append to the commands path
     * @return string The full path to the commands directory with optional suffix
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function commands_path(string $suffix_path = ''): string
    {
        return app()->getCommandPath() . $suffix_path;
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the full path to the application's storage directory.
     *
     * @param string $suffix_path Optional suffix to append to the storage path
     * @return string The full path to the storage directory with optional suffix
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function storage_path(string $suffix_path = ''): string
    {
        return app()->getStoragePath() . $suffix_path;
    }
}

if (!function_exists('cache_path')) {
    /**
     * Get the full path to the application's cache directory.
     *
     * @param string $suffix_path Optional suffix to append to the cache path
     * @return string The full path to the cache directory with optional suffix
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     * @deprecated Since version 0.32, use compiled_view_path() instead.
     */
    function cache_path(string $suffix_path = ''): string
    {
        return app()->getCachePath() . $suffix_path;
    }
}

if (!function_exists('compiled_view_path')) {
    /**
     * Get the path to the compiled views' directory.
     *
     * @return string The compiled views path
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function compiled_view_path(): string
    {
        return app()->getCompiledViewPath();
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the full path to the application's config directory.
     *
     * @param string $suffix_path Optional suffix to append to the config path
     * @return string The full path to the config directory with optional suffix
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function config_path(string $suffix_path = ''): string
    {
        return app()->getConfigPath() . $suffix_path;
    }
}

if (!function_exists('middleware_path')) {
    /**
     * Get the full path to the application's middleware directory.
     *
     * @param string $suffix_path Optional suffix to append to the middleware path
     * @return string The full path to the middleware directory with optional suffix
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function middleware_path(string $suffix_path = ''): string
    {
        return app()->getMiddlewarePath() . $suffix_path;
    }
}

if (!function_exists('provider_path')) {
    /**
     * Get the full path to the application's providers directory.
     *
     * @param string $suffix_path Optional suffix to append to the provider path
     * @return string The full path to the providers directory with optional suffix
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function provider_path(string $suffix_path = ''): string
    {
        return app()->getProviderPath() . $suffix_path;
    }
}

if (!function_exists('migration_path')) {
    /**
     * Get the full path to the application's migration directory.
     *
     * @param string $suffix_path Optional suffix to append to the migration path
     * @return string The full path to the migrations directory with optional suffix
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function migration_path(string $suffix_path = ''): string
    {
        return app()->getMigrationPath() . $suffix_path;
    }
}

if (!function_exists('seeder_path')) {
    /**
     * Get the full path to the application's seeder directory.
     *
     * @param string $suffix_path Optional suffix to append to the seeder path
     * @return string The full path to the seeders directory with optional suffix
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function seeder_path(string $suffix_path = ''): string
    {
        return app()->getSeederPath() . $suffix_path;
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the base path of the application.
     *
     * @param string $insert_path Optional string to append at the end of the base path
     * @return string The base path with optional appended string
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function base_path(string $insert_path = ''): string
    {
        return app()->getBasePath() . $insert_path;
    }
}

if (!function_exists('app_env')) {
    /**
     * Get the current application environment mode.
     *
     * @return string The environment name (e.g., "production", "development")
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function app_env(): string
    {
        return app()->getEnvironment();
    }
}

if (!function_exists('is_production')) {
    /**
     * Determine if the application is running in production mode.
     *
     * @return bool True if in production mode, false otherwise
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function is_production(): bool
    {
        return app()->isProduction();
    }
}

if (!function_exists('is_dev')) {
    /**
     * Determine if the application is running in development mode.
     *
     * @return bool True if in development mode, false otherwise
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the requested entry is not found in the container.
     */
    function is_dev(): bool
    {
        return app()->isDev();
    }
}

if (!function_exists('app')) {
    /**
     * Get the Application container instance.
     *
     * @throws ApplicationNotAvailableException If the Application instance is not available
     * @return Application The application instance
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
     * Get the application configuration as an immutable collection.
     *
     * @return CollectionImmutable<string, mixed> The configuration collection
     * @throws DependencyException If a dependency error occurs
     * @throws NotFoundException If configuration is not found
     */
    function config(): CollectionImmutable
    {
        return new CollectionImmutable(app()->get('config'));
    }
}

if (!function_exists('view')) {
    /**
     * Render a view using the custom template engine.
     *
     * @param string               $view_path The path or name of the view template
     * @param array<string, mixed> $data      Data to pass to the view
     * @param array<string, mixed> $option    Additional options such as 'status' and 'header'
     * @return Response The HTTP response object with rendered content
     * @throws DependencyException If a dependency error occurs
     * @throws NotFoundException If the view or related service is not found
     */
    function view(string $view_path, array $data = [], array $option = []): Response
    {
        /** @var Response $status_code */
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
     * Get resources via Vite using one or more entry points.
     *
     * @param string ...$entry_points One or multiple entry point strings
     * @return array<string, string>|string Resource URLs keyed by entry points, or single resource string
     * @throws DependencyException If Vite service is not available
     * @throws NotFoundException If requested resource is not found
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
     * Redirect to a named route, optionally with dynamic parameters.
     *
     * @param string   $route_name Name of the route to redirect to
     * @param string[] $parameter  Parameters to fill route placeholders
     * @return RedirectResponse The redirect response object
     * @throws Exception If parameters do not match route patterns
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
     * Redirect to a specified URL.
     *
     * @param string $url URL to redirect to
     * @return RedirectResponse The redirect response object
     */
    function redirect(string $url): RedirectResponse
    {
        return new RedirectResponse($url);
    }
}

if (!function_exists('abort')) {
    /**
     * Abort the current request and respond with an HTTP exception.
     *
     * @param int                   $code    HTTP status code to abort with
     * @param string                $message Optional message
     * @param array<string, string> $headers Optional headers to send
     * @return void
     */
    function abort(int $code, string $message = '', array $headers = []): void
    {
        app()->abort($code, $message, $headers);
    }
}
