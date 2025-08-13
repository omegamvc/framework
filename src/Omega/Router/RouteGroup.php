<?php

declare(strict_types=1);

namespace Omega\Router;

use Closure;

class RouteGroup
{
    /** @var callable */
    private $setup;

    /** @var callable */
    private $cleanup;

    public function __construct(Closure $setup, Closure $cleanup)
    {
        $this->setup   = $setup;
        $this->cleanup = $cleanup;
    }

    /**
     * @template T
     *
     * @param callable(): T $callback
     * @return T
     */
    public function group(callable $callback)
    {
        // call stack
        ($this->setup)();
        $result = ($callback)();
        ($this->cleanup)();

        return $result;
    }
}
