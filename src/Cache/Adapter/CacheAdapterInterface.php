<?php

/**
 * Part of Omega - Cache Package.
 * php version 8.2
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Cache\Adapter;

/**
 * Cache adapter interface.
 *
 * The `CacheAdapterInterface` defines the contract for cache adapters, allowing different
 * cache storage systems to be used interchangeably within the framework.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Adapter
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
interface CacheAdapterInterface
{
    /**
     * Check if a value exists in the cache.
     *
     * @param string $key Holds the cache key to check.
     * @return bool Returns true if the key exists in the cache, otherwise false.
     */
    public function has(string $key): bool;

    /**
     * Retrieve a cached value by its key.
     *
     * @param string $key     Holds the cache key to retrieve.
     * @param mixed  $default Holds the default value to return if the key is not found.
     * @return mixed Return the cached value if found, otherwise the default value.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Store a value in the cache with an optional expiration time.
     *
     * @param string   $key     Holds the cache key to store.
     * @param mixed    $value   Holds the value to store in the cache.
     * @param int|null $seconds Holds the number of seconds until the cache item expires (null for no expiration).
     * @return $this Return the cache adapter instance.
     */
    public function put(string $key, mixed $value, ?int $seconds = null): static;

    /**
     * Remove a single cache value by its key.
     *
     * @param string $key Holds the cache key to remove.
     * @return $this Return the cache adapter instance.
     */
    public function forget(string $key): static;

    /**
     * Remove all cached values.
     *
     * @return $this Return the cache adapter instance.
     */
    public function flush(): static;
}
