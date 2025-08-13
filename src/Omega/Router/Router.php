<?php

declare(strict_types=1);

namespace Omega\Router;

use Closure;
use Exception;

use function array_key_exists;
use function array_keys;
use function array_values;
use function call_user_func_array;
use function str_replace;

class Router
{
    /** @var Route[] */
    private static array $routes = [];

    /** @var ?callable(string): mixed */
    private static $pathNotFound;

    /** @var ?callable(string, string): mixed */
    private static $methodNotAllowed;

    /** @var array<string, string|string[]> */
    public static array $group = [
        'prefix'     => '',
        'middleware' => [],
    ];
    /** @var Route|null */
    private static ?Route $current = null;

    /**
     * Alias router param to readable regex url.
     *
     * @var array<string, string>
     */
    public static array $patterns = [
        '(:id)'   => '(\d+)',
        '(:num)'  => '([0-9]*)',
        '(:text)' => '([a-zA-Z]*)',
        '(:any)'  => '([0-9a-zA-Z_+-]*)',
        '(:slug)' => '([0-9a-zA-Z_-]*)',
        '(:all)'  => '(.*)',
    ];

    /**
     * Replace alias to regex.
     *
     * @param string $url Alias pattern url
     * @return string Pattern regex
     */
    public static function mapPatterns(string $url): string
    {
        $userPattern  = array_keys(self::$patterns);
        $allowPattern = array_values(self::$patterns);

        return str_replace($userPattern, $allowPattern, $url);
    }

    /**
     * Adding new router using array of router.
     *
     * @param Route[] $route Router array format (expression, function, method)
     */
    public static function addRoutes(array $route): void
    {
        if (isset($route['expression'])
        && isset($route['function'])
        && isset($route['method'])) {
            self::$routes[] = new Route($route);
        }
    }

    /**
     * Remove router using router name.
     */
    public static function removeRoutes(string $routeName): void
    {
        foreach (self::$routes as $name => $route) {
            if ($route['name'] === $routeName) {
                unset(self::$routes[$name]);
            }
        }
    }

    /**
     * Change exists route using router name.
     *
     * @param string $routeName
     * @param Route  $newRoute
     */
    public static function changeRoutes(string $routeName, Route $newRoute): void
    {
        foreach (self::$routes as $name => $route) {
            if ($route['name'] === $routeName) {
                self::$routes[$name] = $newRoute;
                break;
            }
        }
    }

    /**
     * Merge router array from other router array.
     *
     * @param Route[][] $arrayRoutes
     */
    public static function mergeRoutes(array $arrayRoutes): void
    {
        foreach ($arrayRoutes as $route) {
            self::addRoutes($route);
        }
    }

    /**
     * Get routes array.
     *
     * @return Route[] Routes array
     */
    public static function getRoutes(): array
    {
        $routes = [];
        foreach (self::$routes as $route) {
            $routes[] = $route->route();
        }

        return $routes;
    }

    /**
     * @return Route[]
     */
    public static function getRoutesRaw(): array
    {
        return self::$routes;
    }

    /**
     * Get current route.
     *
     * @return Route|null
     */
    public static function getCurrent(): ?Route
    {
        return self::$current;
    }

    /**
     * Reset all property to be null.
     */
    public static function Reset(): void
    {
        self::$routes           = [];
        self::$pathNotFound     = null;
        self::$methodNotAllowed = null;
        self::$group            = [
            'prefix'  => '',
            'as'      => '',
        ];
    }

    /**
     * Grouping routes using same prefix.
     *
     * @param string $prefix Prefix of router expression
     */
    public static function prefix(string $prefix): RouteGroup
    {
        $previousPrefix = self::$group['prefix'];

        return new RouteGroup(
            // set up
            function () use ($prefix, $previousPrefix) {
                Router::$group['prefix'] = $previousPrefix . $prefix;
            },
            // reset
            function () use ($previousPrefix) {
                Router::$group['prefix'] = $previousPrefix;
            }
        );
    }

    /**
     * Run middleware before run group route.
     *
     * @param array<int, class-string> $middlewares Middleware
     */
    public static function middleware(array $middlewares): RouteGroup
    {
        $resetGroup = self::$group;

        return new RouteGroup(
            // load middleware
            function () use ($middlewares) {
                self::$group['middleware'] = $middlewares;
            },
            // close middleware
            function () use ($resetGroup) {
                self::$group = $resetGroup;
            }
        );
    }

    public static function name(string $name): RouteGroup
    {
        return new RouteGroup(
            // setup
            function () use ($name) {
                Router::$group['as'] = $name;
            },
            // reset
            function () {
                Router::$group['as'] = '';
            }
        );
    }

    public static function controller(string $className): RouteGroup
    {
        // backup current route
        $resetGroup = self::$group;

        return new RouteGroup(
            // setup
            function () use ($className) {
                self::$group['controller'] = $className;
            },
            // reset
            function () use ($resetGroup) {
                self::$group = $resetGroup;
            }
        );
    }

    /**
     * @param array<string, string|string> $setupGroup
     * @param Closure                     $group
     */
    public static function group(array $setupGroup, Closure $group): void
    {
        self::$group['middleware'] ??= [];

        // backup current
        $resetGroup = self::$group;

        $routeGroup = new RouteGroup(
            // setup
            function () use ($setupGroup) {
                foreach ((array) self::$group['middleware'] as $middleware) {
                    $setupGroup['middleware'][] = $middleware;
                }
                self::$group = $setupGroup;
            },
            // reset
            function () use ($resetGroup) {
                self::$group = $resetGroup;
            }
        );

        $routeGroup->group($group);
    }

    public static function has(string $routeName): bool
    {
        foreach (self::$routes as $route) {
            if ($routeName === $route['name']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Redirect to another route.
     *
     * @throws Exception
     */
    public static function redirect(string $to): Route
    {
        foreach (self::$routes as $name => $route) {
            if ($route['name'] === $to) {
                return self::$routes[$name];
            }
        }

        throw new Exception('Route name doest exist.');
    }

    /**
     * @param string                  $uri
     * @param class-string            $className
     * @param array<string, string[]> $setup
     * @return ResourceControllerCollection
     */
    public static function resource(string $uri, string $className, array $setup = []): ResourceControllerCollection
    {
        $setup['map'] ??= ResourceController::method();

        $resource = new ResourceController($uri, $className, $setup['map']);

        if (isset($setup['only'])) {
            $resource->only($setup['only']);
        }
        if (isset($setup['except'])) {
            $resource->except($setup['except']);
        }

        $resource->get()->each(function ($route) {
            self::$routes[] = $route;

            return true;
        });

        $router = new ResourceControllerCollection($className);

        if (array_key_exists('missing', $setup)) {
            $router->missing($setup['missing']);
        }

        return $router;
    }

    /**
     * Function used to add a new route.
     *
     * @param string|string[] $method   Methods allow
     * @param string                   $uri      Route string or expression
     * @param callable|string|string[] $callback Function to call if route with allowed method is found
     * @return Route
     */
    public static function match(array|string $method, string $uri, array|callable|string $callback): Route
    {
        $uri = self::$group['prefix'] . $uri;
        if (isset(self::$group['controller']) && is_string($callback)) {
            $callback = [self::$group['controller'], $callback];
        }
        $middleware = self::$group['middleware'] ?? [];

        return self::$routes[] = new Route([
            'method'      => $method,
            'uri'         => $uri,
            'expression'  => self::mapPatterns($uri),
            'function'    => $callback,
            'middleware'  => $middleware,
        ]);
    }

    /**
     * Function used to add a new route [any method].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     * @return Route
     */
    public static function any(string $expression, mixed $function): Route
    {
        return self::match(['get', 'head', 'post', 'put', 'patch', 'delete', 'options'], $expression, $function);
    }

    /**
     * Function used to add a new route [method: get].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     * @return Route
     */
    public static function get(string $expression, mixed $function): Route
    {
        return self::match(['get', 'head'], $expression, $function);
    }

    /**
     * Function used to add a new route [method: post].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     * @return Route
     */
    public static function post(string $expression, mixed $function): Route
    {
        return self::match('post', $expression, $function);
    }

    /**
     * Function used to add a new route [method: put].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     * @return Route
     */
    public static function put(string $expression, mixed $function): Route
    {
        return self::match('put', $expression, $function);
    }

    /**
     * Function used to add a new route [method: patch].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     * @return Route
     */
    public static function patch(string $expression, mixed $function): Route
    {
        return self::match('patch', $expression, $function);
    }

    /**
     * Function used to add a new route [method: delete].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     * @return Route
     */
    public static function delete(string $expression, mixed $function): Route
    {
        return self::match('delete', $expression, $function);
    }

    /**
     * Function used to add a new route [method: options].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     * @return Route
     */
    public static function options(string $expression, mixed $function): Route
    {
        return self::match('options', $expression, $function);
    }

    /**
     * Result when route expression not register/found.
     *
     * @param callable $function Function to be Call
     */
    public static function pathNotFound(mixed $function): void
    {
        self::$pathNotFound = $function;
    }

    /**
     * Result when route method not match/allowed.
     *
     * @param callable $function Function to be Call
     */
    public static function methodNotAllowed(mixed $function): void
    {
        self::$methodNotAllowed = $function;
    }

    /**
     * Run/execute routes.
     *
     * @param string $basePath             Base Path
     * @param bool   $caseMatters          Case-sensitive matters
     * @param bool   $trailingSlashMatters Trailing slash matters
     * @param bool   $multiMatch           Return multi route
     */
    public static function run(
        string $basePath = '',
        bool $caseMatters = false,
        bool $trailingSlashMatters = false,
        bool $multiMatch = false
    ): mixed {
        $dispatcher = RouteDispatcher::dispatchFrom($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], self::$routes);

        $dispatch = $dispatcher
            ->basePath($basePath)
            ->caseMatters($caseMatters)
            ->trailingSlashMatters($trailingSlashMatters)
            ->multiMatch($multiMatch)
            ->run(
                fn ($current, $params) => call_user_func_array($current, $params),
                fn ($path)             => call_user_func_array(self::$pathNotFound, [$path]),
                fn ($path, $method)    => call_user_func_array(self::$methodNotAllowed, [$path, $method])
            );

        self::$current = $dispatcher->current();

        // run middleware
        $middlewareUsed = [];
        foreach ($dispatch['middleware'] as $middleware) {
            if (in_array($middleware, $middlewareUsed)) {
                continue;
            }

            $middlewareUsed[]  = $middleware;
            $middlewareClass   = new $middleware();
            $middlewareClass->handle();
        }

        return call_user_func_array($dispatch['callable'], $dispatch['params']);
    }
}
