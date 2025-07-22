<?php

/**
 * Part of Omega - Cache Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Cache;

use Closure;
use DateInterval;

/**
 * Interface CacheInterface
 *
 * Defines a standard contract for interacting with a caching system.
 *
 * Implementations of this interface provide methods to retrieve, store,
 * delete, and manage cached items either individually or in batches.
 * It also includes support for TTL (Time-To-Live), atomic increment/decrement,
 * and lazy-loading via the `remember()` helper.
 *
 * @category  Omega
 * @package   Cache
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
interface CacheInterface
{
    /**
     * Fetches a value from the cache.
     *
     * @param string $key     The unique key identifying the cached item.
     * @param mixed  $default The default value to return if the item is not found.
     * @return mixed The cached value or $default if the key does not exist.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Stores a value in the cache under a unique key, optionally with an expiration TTL.
     *
     * @param string                    $key   The key under which to store the value.
     * @param mixed                     $value The value to store.
     * @param int|DateInterval|null     $ttl   Optional. Time-to-live in seconds or as a DateInterval.
     * @return bool True on success, false on failure.
     */
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool;

    /**
     * Deletes a value from the cache.
     *
     * @param string $key The key of the item to remove.
     * @return bool True if the item was successfully removed, false otherwise.
     */
    public function delete(string $key): bool;

    /**
     * Clears all entries from the cache.
     *
     * @return bool True on success, false on failure.
     */
    public function clear(): bool;

    /**
     * Retrieves multiple values from the cache using a list of keys.
     *
     * @param iterable<string> $keys    The list of keys to retrieve.
     * @param mixed            $default The default value for keys that are not found.
     * @return iterable<string, mixed> An iterable of key-value pairs.
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable;

    /**
     * Stores multiple key-value pairs in the cache, optionally with a TTL.
     *
     * @param iterable<string, mixed>  $values A list of key-value pairs to store.
     * @param int|DateInterval|null    $ttl    Optional TTL to apply to all items.
     * @return bool True on success, false on failure.
     */
    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool;

    /**
     * Deletes multiple items from the cache.
     *
     * @param iterable<string> $keys The list of keys to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteMultiple(iterable $keys): bool;

    /**
     * Checks whether a given key exists in the cache.
     *
     * @param string $key The cache key to check.
     * @return bool True if the item exists, false otherwise.
     */
    public function has(string $key): bool;

    /**
     * Atomically increases a numeric value in the cache.
     *
     * @param string $key   The cache key.
     * @param int    $value The amount to increment by.
     * @return int The new value after incrementing.
     */
    public function increment(string $key, int $value): int;

    /**
     * Atomically decreases a numeric value in the cache.
     *
     * @param string $key   The cache key.
     * @param int    $value The amount to decrement by.
     * @return int The new value after decrementing.
     */
    public function decrement(string $key, int $value): int;

    /**
     * Retrieves an item from the cache or stores the result of the callback if it does not exist.
     *
     * @param string                    $key      The cache key.
     * @param Closure                   $callback The callback that generates the value if not cached.
     * @param int|DateInterval|null     $ttl      Optional TTL for the cached value.
     * @return mixed The cached value, or the result of the callback if not found.
     */
    public function remember(string $key, Closure $callback, int|DateInterval|null $ttl = null): mixed;
}
