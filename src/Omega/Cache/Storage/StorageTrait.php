<?php

/**
 * Part of Omega - Cache Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Cache\Storage;

use DateInterval;
use DateTimeInterface;

use function microtime;
use function round;
use function time;

/**
 * Trait StorageTrait
 *
 * Provides shared time-related logic for cache storage drivers, such as precise
 * microtime calculation and cache expiration timestamp computation.
 *
 * This trait is designed to be used by concrete cache storage implementations
 * (e.g. FileStorage, RedisStorage) that need consistent time handling and
 * metadata generation for cache items.
 *
 * It defines a concrete helper method for microtime calculation and two abstract
 * methods to be implemented by the storage classes: one for retrieving cache
 * metadata (`getInfo`) and one for calculating expiration timestamps based on
 * TTL values.
 *
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Storage
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
trait StorageTrait
{
    /**
     * Calculates a precise timestamp including microseconds based on the current time.
     *
     * This value is primarily used to track the creation or modification time
     * of cache items, allowing for more accurate expiration and profiling data.
     *
     * @return float Returns the calculated timestamp with millisecond precision.
     */
    protected function createMtime(): float
    {
        $currentTime = time();
        $microtime   = microtime(true);

        $fractionalPart = $microtime - $currentTime;

        if ($fractionalPart >= 1) {
            $currentTime += (int) $fractionalPart;
            $fractionalPart -= (int) $fractionalPart;
        }

        $mtime = $currentTime + $fractionalPart;

        return round($mtime, 3);
    }

    /**
     * Retrieves metadata information about a specific cache entry.
     *
     * Implementations should return an associative array that includes at least
     * the stored value and optionally other metadata such as creation time
     * or modification time.
     *
     * Example:
     * ```php
     * [
     *   'key_name' => [
     *       'value'     => 'cached_value',
     *       'timestamp' => 1697123456,
     *       'mtime'     => 1697123456.123
     *   ]
     * ]
     * ```
     *
     * @param string $key The cache item key to retrieve information for.
     * @return array<string, array{value: mixed, timestamp?: int, mtime?: float}>
     *                Returns an array containing metadata for the given key.
     */
    abstract public function getInfo(string $key): array;

    /**
     * Calculates the cache item's expiration timestamp based on the provided TTL.
     *
     * Implementations should convert the TTL (in seconds or as a DateInterval)
     * into a UNIX timestamp representing the moment when the cache item expires.
     * If the TTL is `null`, the cache item should be considered persistent.
     *
     * @param int|DateInterval|DateTimeInterface|null $ttl The time-to-live value.
     *        - `int`: Number of seconds until expiration.
     *        - `DateInterval`: A relative interval added to the current time.
     *        - `DateTimeInterface`: A specific expiration moment.
     *        - `null`: No expiration (persistent cache).
     *
     * @return int Returns the UNIX timestamp representing the expiration time.
     */
    abstract protected function calculateExpirationTimestamp(int|DateInterval|DateTimeInterface|null $ttl): int;
}
