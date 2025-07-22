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

namespace Omega\Cache\Storage;

use DateInterval;
use DateTimeInterface;
use Omega\Cache\CacheInterface;

/**
 * Interface StorageInterface
 *
 * Defines the extended contract for cache storage backends.
 *
 * This interface extends a basic CacheInterface by including methods
 * for inspecting cache metadata (e.g., timestamps and modification times),
 * calculating expiration times, and generating precise timestamps
 * for versioning or invalidation logic.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Storage
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
interface StorageInterface extends CacheInterface
{
    /**
     * Retrieve detailed metadata for a given cache key.
     *
     * Returns an array containing the cached value and its associated metadata,
     * including the optional expiration timestamp and last modified time.
     *
     * @param string $key The cache key.
     * @return array{value: mixed, timestamp?: int, mtime?: float} The metadata for the cached entry.
     */
    public function getInfo(string $key): array;

    /**
     * Calculate the expiration timestamp based on the provided TTL value.
     *
     * Accepts a TTL in seconds, a DateInterval, a DateTimeInterface representing
     * the absolute expiration time, or null to use the default TTL.
     *
     * @param int|DateInterval|DateTimeInterface|null $ttl Time-to-live value.
     * @return int The resulting expiration timestamp as a Unix timestamp.
     */
    public function calculateExpirationTimestamp(int|DateInterval|DateTimeInterface|null $ttl): int;

    /**
     * Generate a high-precision modification timestamp.
     *
     * Typically used to track the last update time of a cache entry,
     * especially useful for cache invalidation strategies.
     *
     * @return float The current Unix timestamp with microseconds.
     */
    public function createMtime(): float;
}
