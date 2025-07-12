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

use function array_combine;
use function array_fill;
use function array_push;
use function count;
use function preg_match_all;
use function preg_replace;
use function preg_replace_callback;
use function rtrim;
use function str_contains;
use function str_ends_with;
use function trim;

/**
 * Route class.
 *
 * The `Route` class represents an individual route in the routing system.
 *
 * @category    Omega
 * @package     Routing
 * @link        https://omegamvc.github.io
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
class Route
{
    /**
     * Array of route parameters.
     *
     * @var array Holds an array of route parameters.
     */
    protected array $parameters = [];

    /**
     * Route class constructor.
     *
     * @param string      $method  Holds the HTTP method associated with the route.
     * @param string      $path    Holds the path pattern for the route.
     * @param mixed       $handler Holds the handler for the route.
     * @param string|null $name    Holds the route name or null.
     * @return void
     */
    public function __construct(
        protected string $method,
        protected string $path,
        protected mixed $handler,
        protected ?string $name = null
    ) {
    }

    /**
     * Get the HTTP method associated with the route.
     *
     * @return string Returns the route method.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get the path pattern for the route.
     *
     * @return string Returns the route path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get the parameters associated with the route.
     *
     * @return array Returns an array of route parameters.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get or set the name of the route.
     *
     * @param string|null $name Holds the route name.
     * @return $this|string|null
     */
    public function getName(string $name = null): static|string|null
    {
        if ($name) {
            $this->name = $name;

            return $this;
        }

        return $this->name;
    }

    /**
     * Alias for getName().
     *
     * @param string|null $name Holds the route name.
     * @return $this|string|null
     */
    public function name(?string $name = null): static|string|null
    {
        return $this->getName($name);
    }

    /**
     * Checks if the route matches a given HTTP method and path.
     *
     * @param string $method Holds the HTTP method to match.
     * @param string $path   Holds the path to match.
     * @return bool Returns true if the route matches, false otherwise.
     */
    public function matches(string $method, string $path): bool
    {
        if (
            $this->method  === $method
            && $this->path === $path
        ) {
            return true;
        }

        $parameterNames = [];

        $pattern = $this->normalisePath($this->path);

        $pattern = preg_replace_callback(
            '#{([^}]+)}/#',
            function (array $found) use (&$parameterNames) {
                array_push($parameterNames, rtrim($found[1], '?'));

                if (str_ends_with($found[1], '?')) {
                    return '([^/]*)(?:/?)';
                }

                return '([^/]+)/';
            },
            $pattern
        );

        if (
            ! str_contains($pattern, '+')
            && ! str_contains($pattern, '*')
        ) {
            return false;
        }

        preg_match_all("#$pattern#", $this->normalisePath($path), $matches);

        $parameterValues = [];

        if (count($matches[1]) > 0) {
            foreach ($matches[1] as $value) {
                if ($value) {
                    array_push($parameterValues, $value);

                    continue;
                }

                array_push($parameterValues, null);
            }

            $emptyValues = array_fill(0, count($parameterNames), false);
            $parameterValues += $emptyValues;

            $this->parameters = array_combine($parameterNames, $parameterValues);

            return true;
        }

        return false;
    }

    /**
     * Normalizes the path by removing leading and trailing slashes.
     *
     * @param string $path Holds the path to normalize.
     * @return string Returns the normalized path.
     */
    private function normalisePath(string $path): string
    {
        $path = trim($path, '/');
        $path = "/$path/";

        return preg_replace('/[\/]{2,}/', '/', $path);
    }

    /**
     * Dispatches the route handler.
     *
     * This method invokes the registered handler for the route, typically a controller action.
     *
     * @return mixed The result of the route handler.
     */
    public function dispatch(): mixed
    {
        if (is_array($this->handler)) {
            [ $class, $method ] = $this->handler;

            if (is_string($class)) {
                return app()->call([ new $class(), $method ]);
            }

            return app()->call([ $class, $method ]);
        }

        return app()->call($this->handler);
    }
}
