<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use Closure;
use DateInterval;
use Omega\Cache\CacheInterface;

/**
 * @method static self setDefaultDriver(CacheInterface $driver)
 * @method static self setDriver(string $driver_name, CacheInterface $driver)
 * @method static CacheInterface driver(?string $driver = null)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool set(string $key, mixed $value, int|DateInterval|null $ttl = null)
 * @method static bool delete(string $key)
 * @method static bool clear()
 * @method static iterable getMultiple(iterable $keys, mixed $default = null)
 * @method static bool setMultiple(iterable $values, int|DateInterval|null $ttl = null)
 * @method static bool deleteMultiple(iterable $keys)
 * @method static bool has(string $key)
 * @method static int increment(string $key, int $value)
 * @method static int decrement(string $key, int $value)
 * @method static mixed remember(string $key, Closure $callback, int|DateInterval|null $ttl = null)
 */
final class Cache extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'cache';
    }
}
