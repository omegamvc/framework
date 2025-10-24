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
use DateTimeImmutable;
use DateTimeInterface;

use function array_key_exists;
use function time;

/**
 * In-memory array-based cache storage implementation.
 *
 * This class provides a fast, ephemeral caching mechanism where all entries
 * are stored in memory using an internal array. It is ideal for testing
 * or short-lived caching scenarios and does not persist data between requests.
 *
 * Cache entries include metadata such as value, expiration timestamp, and
 * modification time. Expired items are automatically invalidated upon retrieval.
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
class ArrayStorage extends AbstractStorage
{
    use StorageTrait;

    /**
     * Internal array holding cached items and their metadata.
     *
     * Format:
     * ```php
     * [
     *   'key_name' => [
     *       'value'     => mixed,
     *       'timestamp' => int,    // expiration time
     *       'mtime'     => float,  // creation/modification time
     *   ],
     * ]
     * ```
     *
     * @var array<string, array{value: mixed, timestamp?: int, mtime?: float}>
     */
    protected array $storage = [];

    /**
     * Create a new ArrayStorage instance.
     *
     * @param int $defaultTTL The default time-to-live (in seconds) for cache items.
     */
    public function __construct(private readonly int $defaultTTL = 3_600)
    {
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
    public function getInfo(string $key): array
    {
        return $this->storage[$key] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (false === array_key_exists($key, $this->storage)) {
            return $default;
        }

        $item = $this->storage[$key];

        $expiresAt = $item['timestamp'] ?? 0;

        if ($this->isExpired($expiresAt)) {
            $this->delete($key);

            return $default;
        }

        return $item['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool
    {
        $this->storage[$key] = [
            'value'     => $value,
            'timestamp' => $this->calculateExpirationTimestamp($ttl),
            'mtime'     => $this->createMtime(),
        ];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        if ($this->has($key)) {
            unset($this->storage[$key]);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $this->storage = [];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return false;
    }


    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->storage);
    }

    /**
     * {@inheritdoc}
     */
    public function increment(string $key, int $value): int
    {
        if (false === $this->has($key)) {
            $this->set($key, $value, 0);

            return $this->storage[$key]['value'];
        }

        $this->storage[$key]['value'] = ((int) $this->storage[$key]['value']) + $value;

        return $this->storage[$key]['value'];
    }

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
    protected function calculateExpirationTimestamp(int|DateInterval|DateTimeInterface|null $ttl): int
    {
        if ($ttl instanceof DateInterval) {
            return new DateTimeImmutable()->add($ttl)->getTimestamp();
        }

        if ($ttl instanceof DateTimeInterface) {
            return $ttl->getTimestamp();
        }

        $ttl ??= $this->defaultTTL;

        return new DateTimeImmutable()->add(new DateInterval("PT{$ttl}S"))->getTimestamp();
    }

    /**
     * Determines if a given timestamp indicates an expired cache item.
     *
     * @param int $timestamp The expiration timestamp of a cache item.
     * @return bool Returns true if the cache item has expired, false otherwise.
     */
    private function isExpired(int $timestamp): bool
    {
        return $timestamp !== 0 && time() >= $timestamp;
    }
}
