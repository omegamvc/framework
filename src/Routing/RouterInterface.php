<?php

/**
 * Part of Omega - Routing Package.
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
 * Router interface.
 *
 * The `RouterInterface` represents the contract for a router implementation.
 *
 * @category    Omega
 * @package     Routing
 * @link        https://omegamvc.github.io
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
interface RouterInterface
{
    /**
     * Get method.
     *
     * Adds a `GET` route to the router.
     *
     * @param string      $path    Holds the path pattern for the route.
     * @param mixed       $handler Holds the handler for the route.
     * @param string|null $name    Holds the route name or null.
     * @return Route Returns the added Route instance.
     */
    public function get(string $path, mixed $handler, ?string $name = null): Route;

    /**
     * Post method.
     *
     * Adds a `POST` route to the router.
     *
     * @param string      $path    Holds the path pattern for the route.
     * @param mixed       $handler Holds the handler for the route.
     * @param string|null $name    Holds the route name or null.
     * @return Route Returns the added Route instance.
     */
    public function post(string $path, mixed $handler, ?string $name = null): Route;

    /**
     * Put method.
     *
     * Adds a `PUT` route to the router.
     *
     * @param string      $path    Holds the path pattern for the route.
     * @param mixed       $handler Holds the handler for the route.
     * @param string|null $name    Holds the route name or null.
     * @return Route Returns the added Route instance.
     */
    public function put(string $path, mixed $handler, ?string $name = null): Route;

    /**
     * Delete method.
     *
     * Adds a `DELETE` route to the router.
     *
     * @param string      $path    Holds the path pattern for the route.
     * @param mixed       $handler Holds the handler for the route.
     * @param string|null $name    Holds the route name or null.
     * @return Route Returns the added Route instance.
     */
    public function delete(string $path, mixed $handler, ?string $name = null): Route;

    /**
     * Patch method.
     *
     * Adds a `PATCH` route to the router.
     *
     * @param string      $path    Holds the path pattern for the route.
     * @param mixed       $handler Holds the handler for the route.
     * @param string|null $name    Holds the route name or null.
     * @return Route Returns the added Route instance.
     */
    public function patch(string $path, mixed $handler, ?string $name = null): Route;

    /**
     * Options method.
     *
     * Adds an `OPTIONS` route to the router.
     *
     * @param string      $path    Holds the path pattern for the route.
     * @param mixed       $handler Holds the handler for the route.
     * @param string|null $name    Holds the route name or null.
     * @return Route Returns the added Route instance.
     */
    public function options(string $path, mixed $handler, ?string $name = null): Route;

    /**
     * Any method.
     *
     * Adds a route that matches `ANY HTTP` method to the router.
     *
     * @param string      $path    Holds the path pattern for the route.
     * @param mixed       $handler Holds the handler for the route.
     * @param string|null $name    Holds the route name or null.
     * @return Route Returns the added Route instance.
     */
    public function any(string $path, mixed $handler, ?string $name = null): Route;

    /**
     * Dispatches the router, matching the current request and executing the
     * corresponding route handler.
     *
     * @return mixed The result of the route handler.
     */
    public function dispatch(): mixed;
}
