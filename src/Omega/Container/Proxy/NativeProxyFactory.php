<?php

declare(strict_types=1);

namespace Omega\Container\Proxy;

use Closure;
use ReflectionClass;
use ReflectionException;

/**
 * Uses PHP 8.4+'s native support for lazy proxies to generate proxy objects.
 */
class NativeProxyFactory implements ProxyFactoryInterface
{
    /**
     * Creates a new lazy proxy instance of the given class with
     * the given initializer.
     *
     * {@inheritDoc}
     *
     * @throws ReflectionException
     */
    public function createProxy(string $className, Closure $createFunction): object
    {
        $reflector = new ReflectionClass($className);

        return $reflector->newLazyProxy($createFunction);
    }
}
