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

use Closure;
use DateInterval;
use DateTimeInterface;

use function is_null;
use function microtime;
use function round;
use function time;

/**
 * Class AbstractStorage
 *
 * Provides a base implementation for cache storage systems.
 *
 * This abstract class defines common properties and constructor logic
 * used by concrete storage implementations, such as default TTL and
 * optional file system path. It implements the StorageInterface, leaving
 * storage-specific behavior to be defined by subclasses.
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
abstract class AbstractStorage implements StorageInterface
{
    /**
     * Optional storage path (e.g., for file-based caches).
     *
     * May be null for in-memory storage implementations.
     *
     * @var string|null
     */
    public ?string $path = null;

    /**
     * The default time-to-live (TTL) for cache entries, in seconds.
     *
     * Used when no specific TTL is provided during a cache write.
     *
     * @var int
     */
    public int $defaultTtl;

    /**
     * AbstractStorage constructor.
     *
     * Initializes the default TTL and optional storage path.
     *
     * @param array $options {
     *     Optional configuration options for the storage instance.
     *
     *     @type string|null $path The file system path used for storage (e.g., for file-based caches). Defaults to null.
     *     @type int         $ttl  The default time-to-live (TTL) in seconds for cache entries. Defaults to 3600.
     * }
     * @return void
     */
    public function __construct(array $options = [])
    {
        $this->path = $options['path'] ?? null;
        $this->defaultTtl = $options['ttl'] ?? 3_600;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getInfo(string $key): array;

    /**
     * {@inheritdoc}
     */
    abstract public function calculateExpirationTimestamp(int|DateInterval|DateTimeInterface|null $ttl): int;

    /**
     * {@inheritdoc}
     */
    public function createMtime(): float
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
     * {@inheritdoc}
     */
    abstract public function get(string $key, mixed $default = null): mixed;

    /**
     * {@inheritdoc}
     */
    abstract public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool;

    /**
     * {@inheritdoc}
     */
    abstract public function delete(string $key): bool;

    /**
     * {@inheritdoc}
     */
    abstract public function clear(): bool;

    /**
     * {@inheritdoc}
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool;

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $state = null;

        foreach ($keys as $key) {
            $result = $this->delete($key);
            $state  = is_null($state) ? $result : $result && $state;
        }

        return $state ?: false;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function has(string $key): bool;

    /**
     * {@inheritdoc}
     */
    abstract public function increment(string $key, int $value): int;

    /**
     * {@inheritdoc}
     */
    public function decrement(string $key, int $value): int
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * {@inheritdoc}
     */
    public function remember(string $key, Closure $callback, int|DateInterval|null $ttl = null): mixed
    {
        $value = $this->get($key);

        if (null !== $value) {
            return $value;
        }

        $this->set($key, $value = $callback(), $ttl);

        return $value;
    }
}
