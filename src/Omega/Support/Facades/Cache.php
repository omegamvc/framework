<?php

/**
 * Part of Omega - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Facades;

use Closure;
use DateInterval;
use Omega\Cache\CacheInterface;

/**
 * Facade for Cache service.
 *
 * Provides static access to the cache manager and cache operations
 * such as getting, setting, deleting cache items, and managing cache drivers.
 *
 * Magic methods correspond to cache operations available on the underlying cache service.
 *
 * @category   Omega
 * @package    Support
 * @subpackage Facades
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
 * @method static self setDefaultDriver(CacheInterface $driver) Set the default cache driver.
 * @method static self setDriver(string $driverName, CacheInterface $driver) Register a new cache driver.
 * @method static CacheInterface driver(?string $driver = null) Get a specific cache driver instance.
 * @method static mixed get(string $key, mixed $default = null) Retrieve an item from the cache.
 * @method static bool set(string $key, mixed $value, int|DateInterval|null $ttl = null) Store an item in the cache.
 * @method static bool delete(string $key) Delete an item from the cache.
 * @method static bool clear() Clear the entire cache.
 * @method static iterable getMultiple(iterable $keys, mixed $default = null) Retrieve multiple cache items.
 * @method static bool setMultiple(iterable $values, int|DateInterval|null $ttl = null) Store multiple cache items.
 * @method static bool deleteMultiple(iterable $keys) Delete multiple cache items.
 * @method static bool has(string $key) Check if an item exists in the cache.
 * @method static int increment(string $key, int $value) Increment a numeric cache item's value.
 * @method static int decrement(string $key, int $value) Decrement a numeric cache item's value.
 * @method static mixed remember(string $key, Closure $callback, int|DateInterval|null $ttl = null) Retrieve an item or store it if not present.
 */
class Cache extends Facade
{
    /**
     * Get the service accessor key for the cache service.
     *
     * This key is used by the Facade base class to resolve the cache instance
     * from the application container.
     *
     * @return string The cache service accessor key.
     */
    protected static function getAccessor(): string
    {
        return 'cache';
    }
}
