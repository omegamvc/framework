<?php

/**
 * Part of Omega -  Routing Package.
 * php version 8.3
 *
 * @link        https://omegamvc.github.io
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */

declare(strict_types=1);

namespace Omega\Routing;

/**
 * Abstract router class.
 *
 * The `AbstractRouter` class serves as a foundation for implementing custom routers within
 * the Omega Routing Package. It provides a set of methods to add routes for different
 * HTTP methods and defines the dispatching behavior, allowing derived classes to handle
 * route matching and execution of associated handlers.
 *
 * @category    Omega
 * @package     Routing
 * @link        https://omegamvc.github.io
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
abstract class AbstractRouter implements RouterInterface
{
    /**
     * Routes array.
     *
     * @var array Holds an array of routes.
     */
    public array $routes = [];

    /**
     * {@inheritdoc}
     */
    public function get(string $path, mixed $handler, ?string $name = null): Route
    {
        return $this->addRoute('GET', $path, $handler, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function post(string $path, mixed $handler, ?string $name = null): Route
    {
        return $this->addRoute('POST', $path, $handler, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $path, mixed $handler, ?string $name = null): Route
    {
        return $this->addRoute('PUT', $path, $handler, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $path, mixed $handler, ?string $name = null): Route
    {
        return $this->addRoute('DELETE', $path, $handler, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function patch(string $path, mixed $handler, ?string $name = null): Route
    {
        return $this->addRoute('PATCH', $path, $handler, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function options(string $path, mixed $handler, ?string $name = null): Route
    {
        return $this->addRoute('OPTIONS', $path, $handler, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function any(string $path, mixed $handler, ?string $name = null): Route
    {
        return $this->addRoute('GET|POST|PUT|DELETE|PATCH|OPTIONS', $path, $handler, $name);
    }

    /**
     * Add a route to the router.
     *
     * @param string      $method  Holds the HTTP method for the route.
     * @param string      $path    Holds the path pattern for the route.
     * @param mixed       $handler Holds the handler for the route.
     * @param string|null $name    Holds the route name or null.
     * @return Route Return the added Route instance.
     */
    private function addRoute(string $method, string $path, mixed $handler, ?string $name = null): Route
    {
        return $this->routes[] = new Route($method, $path, $handler, $name);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed The result of the route handler.
     */
    abstract public function dispatch(): mixed;
}
