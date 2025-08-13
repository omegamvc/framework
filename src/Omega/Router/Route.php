<?php

declare(strict_types=1);

namespace Omega\Router;

use ArrayAccess;
use Exception;
use ReturnTypeWillChange;

use function sprintf;

/**
 * @implements ArrayAccess<string, mixed>
 */
class Route implements ArrayAccess
{
    /** @var array<string, mixed> */
    private array $route;

    private string $prefixName;

    /**
     * @param array<string, mixed> $route
     */
    public function __construct(array $route)
    {
        $this->prefixName = Router::$group['as'] ?? '';
        $route['name']    = $this->prefixName;
        $this->route       = $route;
    }

    /**
     * @param string $name
     * @param string[] $arguments
     * @return array<string, mixed>
     * @throws Exception
     */
    public function __call(string $name, array $arguments)
    {
        if ($name === 'route') {
            return $this->route;
        }

        throw new Exception(sprintf("Route %s not registered.", $name));
    }

    /**
     * Set Route name.
     *
     * @param string $name Route name (uniq)
     * @return self
     */
    public function name(string $name): self
    {
        $this->route['name'] = $this->prefixName . $name;

        return $this;
    }

    /**
     * Add middleware this route.
     *
     * @param class-string[] $middlewares Route class-name
     * @return self
     */
    public function middleware(array $middlewares): self
    {
        foreach ($middlewares as $middleware) {
            $this->route['middleware'][] = $middleware;
        }

        return $this;
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param string $offset the offset to assign the value to
     * @param mixed  $value  the value to set
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->route[$offset] = $value;
    }

    /**
     * Whether an offset exists.
     * This method is executed when using isset() or empty().
     *
     * @param string $offset an offset to check for
     * @return bool returns true on success or false on failure
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->route[$offset]);
    }

    /**
     * Unsets an offset.
     *
     * @param string $offset unsets an offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->route[$offset]);
    }

    /**
     * Returns the value at specified offset.
     *
     * @param string $offset the offset to retrieve
     * @return mixed|null Can return all value types
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->route[$offset] ?? null;
    }
}
