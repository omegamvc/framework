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
use FilesystemIterator;
use Omega\Cache\Exceptions\CachePathException;
use Omega\Cache\Exceptions\InvalidValueIncrementException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function array_slice;
use function basename;
use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function is_dir;
use function is_int;
use function is_null;
use function mkdir;
use function serialize;
use function sha1;
use function str_split;
use function time;
use function unlink;
use function unserialize;

use const LOCK_EX;

/**
 * File-based cache storage implementation.
 *
 * This class provides a persistent caching mechanism that stores serialized cache
 * entries as files within a structured directory tree. Each cache key is hashed
 * into a multi-level path to prevent filesystem overload in a single directory.
 *
 * The cache entries include metadata such as value, expiration timestamp, and
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
class FileStorage extends AbstractStorage
{
    use StorageTrait;

    /**
     * Create a new FileStorage instance.
     *
     * Initializes the storage directory and ensures it exists before use.
     * If the given path does not exist, it will be created recursively.
     *
     * @param string $path       The directory path where cache files are stored.
     * @param int    $defaultTTL The default time-to-live (in seconds) applied to cache items.
     * @throws CachePathException If the directory cannot be created or is not writable.
     */
    public function __construct(
        private readonly string $path,
        private readonly int $defaultTTL = 3_600,
    ) {
        if (!is_dir($this->path) && !mkdir($this->path, 0777, true)) {
            throw new CachePathException($this->path);
        }
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
        $filePath = $this->makePath($key);

        if (false === file_exists($filePath)) {
            return [];
        }

        $data = file_get_contents($filePath);

        if (false === $data) {
            return [];
        }

        return unserialize($data);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $filePath = $this->makePath($key);

        if (false === file_exists($filePath)) {
            return $default;
        }

        $data = file_get_contents($filePath);

        if ($data === false) {
            return $default;
        }

        $cacheData = unserialize($data);

        if (time() >= $cacheData['timestamp']) {
            $this->delete($key);

            return $default;
        }

        return $cacheData['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool
    {
        $filePath  = $this->makePath($key);
        $directory = dirname($filePath);

        if (false === is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $cacheData = [
            'value'     => $value,
            'timestamp' => $this->calculateExpirationTimestamp($ttl),
            'mtime'     => $this->createMtime(),
        ];

        $serializedData = serialize($cacheData);

        return file_put_contents($filePath, $serializedData, LOCK_EX) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        $filePath = $this->makePath($key);

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileInfo) {
            $filePath = $fileInfo->getRealPath();

            if (basename($filePath) === '.gitignore') {
                continue;
            }

            $action = $fileInfo->isDir() ? 'rmdir' : 'unlink';
            $action($filePath);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool
    {
        $state = null;

        foreach ($values as $key => $value) {
            $result = $this->set($key, $value, $ttl);
            $state  = is_null($state) ? $result : $result && $state;
        }

        return $state ?: false;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return file_exists($this->makePath($key));
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidValueIncrementException if the current cache value is not an integer and cannot be incremented.
     */
    public function increment(string $key, int $value): int
    {
        if (false === $this->has($key)) {
            $this->set($key, $value, 0);

            return $value;
        }

        $info = $this->getInfo($key);

        $ori = $info['value'] ?? 0;
        $ttl = $info['timestamp'] ?? 0;

        if (false === is_int($ori)) {
            throw new InvalidValueIncrementException('Value increment must be an integer.');
        }

        $result = (int) ($ori + $value);

        $this->set($key, $result, $ttl);

        return $result;
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

        return time() + $ttl;
    }

    /**
     * Generate the full file path for a given cache key.
     *
     * Converts the cache key into a deterministic hashed path to ensure
     * even file distribution and avoid filesystem performance degradation.
     *
     * Example:
     * ```php
     * $path = $this->makePath('user_123');
     * // "/var/cache/12/ab/12ab34cd...etc."
     * ```
     *
     * @param string $key The cache key for which to generate the file path.
     * @return string The fully qualified path to the cache file.
     */
    protected function makePath(string $key): string
    {
        $hash  = sha1($key);
        $parts = array_slice(str_split($hash, 2), 0, 2);

        return $this->path . '/' . implode('/', $parts) . '/' . $hash;
    }
}
