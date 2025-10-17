<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

declare(strict_types=1);

namespace Omega\Router;

use Omega\Text\Str;

use function array_diff_key;
use function array_filter;
use function in_array;
use function method_exists;

class ResourceControllerCollection
{
    private string $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * Expect resource with route name.
     *
     * @param string[] $resources
     */
    public function only(array $resources): void
    {
        $map = array_filter(
            ResourceController::method(),
            fn ($resource) => !in_array($resource, $resources, true)
        );
        foreach ($map as $resource) {
            $name = "{$this->className}.{$resource}";
            if (Router::has($name)) {
                Router::removeRoutes($name);
            }
        }
    }

    /**
     * Expect resource with route name.
     *
     * @param string[] $resources
     */
    public function except(array $resources): void
    {
        foreach ($resources as $resource) {
            $name = "{$this->className}.{$resource}";
            if (Router::has($name)) {
                Router::removeRoutes($name);
            }
        }
    }

    /**
     * Map resource with exits route resource.
     *
     * @param string[] $resources
     */
    public function map(array $resources): void
    {
        $diff = array_diff_key(ResourceController::method(), $resources);
        $this->except($diff);

        foreach (Router::getRoutes() as $route) {
            foreach ($resources as $key => $resource) {
                $name = "{$this->className}.{$key}";
                if ($name === $route['name']) {
                    $route['function'][1] = $resource;
                    Router::changeRoutes($name, $route);
                    break;
                }
            }
        }
    }

    public function missing(callable $callable): self
    {
        foreach (Router::getRoutes() as $route) {
            $name = $route['name'];
            if (Str::startsWith($name, "{$this->className}.")) {
                [$class, $method] = $route['function'];
                if (!method_exists($class, $method)) {
                    $route['function'] = $callable;
                    Router::changeRoutes($name, $route);
                }
            }
        }

        return $this;
    }
}
