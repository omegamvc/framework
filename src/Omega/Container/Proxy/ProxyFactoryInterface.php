<?php

declare(strict_types=1);

namespace Omega\Container\Proxy;

use Closure;

/**
 * Generic interface for proxy factories.
 */
interface ProxyFactoryInterface
{
    /**
     * Creates a new lazy proxy instance of the given class with
     * the given initializer.
     *
     * @param class-string $className name of the class to be proxied
     * @param Closure $createFunction initializer to be passed to the proxy initializer to be passed to the proxy
     */
    public function createProxy(string $className, Closure $createFunction) : object;
}
