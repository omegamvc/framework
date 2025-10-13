<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use Closure;
use DateInterval;
use Omega\Cache\CacheInterface;
use Omega\Cache\CacheManager;

/**
 * @method static CacheManager   setDefaultDriver(CacheInterface $driver)
 * @method static CacheManager   setDriver(string $driver_name, $driver)
 * @method static CacheInterface driver(?string $driver_name = null)
 * @method static mixed          get(string $key, mixed $default = null)
 * @method static bool           set(string $key, mixed $value, DateInterval|int|null $ttl = null)
 * @method static bool           delete(string $key)
 * @method static bool           clear()
 * @method static iterable       getMultiple(iterable $keys, mixed $default = null)
 * @method static bool           setMultiple(iterable $values, DateInterval|int|null $ttl = null)
 * @method static bool           deleteMultiple(iterable $keys)
 * @method static bool           has(string $key)
 * @method static int            increment(string $key, int $value)
 * @method static int            decrement(string $key, int $value)
 * @method static mixed          remember(string $key, Closure $callback, DateInterval|int|null $ttl)
 *
 * @see CacheManager
 */
final class Cache extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'cache';
    }
}
