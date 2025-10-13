<?php

declare(strict_types=1);

namespace Omega\Cache;

use Closure;
use DateInterval;

interface CacheInterface
{
    /**
     * Fetches a value from the cache.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string $key
     * @param mixed $value
     * @param int|DateInterval|null $ttl
     * @return bool
     */
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool;

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool
     */
    public function clear(): bool;

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable<string> $keys a list of keys that can be obtained in a single operation
     * @param mixed $default
     * @return iterable<string, mixed> A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable;

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable<string, mixed> $values
     * @param int|DateInterval|null $ttl
     * @return bool
     */
    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool;

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable<string> $keys a list of string-based keys to be deleted
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool;

    /**
     * Determines whether an item is present in the cache.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @param string $key
     * @param int $value
     * @return int
     */
    public function increment(string $key, int $value): int;

    /**
     * @param string $key
     * @param int $value
     * @return int
     */
    public function decrement(string $key, int $value): int;

    /**
     * @param string $key
     * @param Closure $callback
     * @param int|DateInterval|null $ttl
     * @return mixed
     */
    public function remember(string $key, Closure $callback, int|DateInterval|null $ttl): mixed;
}
