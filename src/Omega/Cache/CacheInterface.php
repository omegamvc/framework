<?php

/**
 * Part of Omega - Cache Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Cache;

use DateInterval;
use Omega\Cache\Exception\InvalidArgumentException;

/**
 * CacheInterface class.
 *
 * The `CacheInterface` defines a standard contract for caching operations within the Omega
 * framework. It provides methods for storing, retrieving, deleting, and checking the existence
 * of cache items, supporting single and multiple key operations.
 *
 * This interface ensures consistency across different cache implementations while allowing
 * flexibility in choosing storage mechanisms. It follows best practices for handling cached
 * data, including support for expiration (TTL) and race condition considerations when checking
 * key existence.
 *
 * @category   Omega
 * @package    Cache
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
interface CacheInterface
{
    /**
     * Fetches a value from the cache.
     *
     * @param string $key     Holds the unique key of this item in the cache.
     * @param mixed  $default Holds the default value to return if the key does not exist.
     * @return mixed Return the value of the item from the cache, or $default in case of cache miss.
     * @throws InvalidArgumentException if the $key string is not a legal value.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                $key   Holds the key of the item to store.
     * @param mixed                 $value Holds the value of the item to store, must be serializable.
     * @param null|int|DateInterval $ttl   Optional. Holds the TTL value of this item. If no value is sent and
     *                                     the driver supports TTL then the library may set a default value
     *                                     for it or let the driver take care of that.
     * @return bool Return true on success and false on failure.
     * @throws InvalidArgumentException if the $key string is not a legal value.
     */
    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool;

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key Holds the unique cache key of the item to delete.
     * @return bool Return true if the item was successfully removed. False if there was an error.
     * @throws InvalidArgumentException if the $key string is not a legal value.
     */
    public function delete(string $key): bool;

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool Return true on success and false on failure.
     */
    public function clear(): bool;

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable<string> $keys    Holds a list of keys that can be obtained in a single operation.
     * @param mixed            $default Holds the default value to return for keys that do not exist.
     * @return iterable<string, mixed> Return a list of key => value pairs. Cache keys that do not exist or
     *                                 are stale will have $default as value.
     * @throws InvalidArgumentException if $keys is neither an array nor a Traversable,
     *                                  or if any of the $keys are not a legal value.
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable;

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable<string, mixed>       $values Holds a list of key => value pairs for a multiple-set operation.
     * @param null|int|DateInterval $ttl    Optional. Return the TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     * @return bool Return true on success and false on failure.
     * @throws InvalidArgumentException if $values is neither an array nor a Traversable,
     *                                  or if any of the $values are not a legal value.
     */
    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool;

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable<string> $keys Holds a list of string-based keys to be deleted.
     * @return bool Return true if the items were successfully removed. False if there was an error.
     * @throws InvalidArgumentException if $keys is neither an array nor a Traversable,
     *                                  or if any of the $keys are not a legal value.
     */
    public function deleteMultiple(iterable $keys): bool;

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     * @return bool Return true if the cache contains an item with the given key, false if not.
     * @throws InvalidArgumentException if the $key string is not a legal value.
     */
    public function has(string $key): bool;
}
