<?php

declare(strict_types=1);

namespace Omega\Cache\Storage;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use FilesystemIterator;
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

class FileStorage extends AbstractStorage
{
    use StorageTrait;

    public function __construct(
        private readonly string $path,
        private readonly int $defaultTTL = 3_600,
    ) {
        if (false === is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    /**
     * Get info of storage.
     *
     * @param string $key
     * @return array<string, array{value: mixed, timestamp?: int, mtime?: float}>
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
     * @throws InvalidValueIncrementException
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
     * @param int|DateInterval|DateTimeInterface|null $ttl
     * @return int
     */
    protected function calculateExpirationTimestamp(int|DateInterval|DateTimeInterface|null $ttl): int
    {
        if ($ttl instanceof DateInterval) {
            return (new DateTimeImmutable())->add($ttl)->getTimestamp();
        }

        if ($ttl instanceof DateTimeInterface) {
            return $ttl->getTimestamp();
        }

        $ttl ??= $this->defaultTTL;

        return time() + $ttl;
    }

    /**
     * Generate the full file path for the given cache key.
     *
     * @param string $key
     * @return string
     */
    protected function makePath(string $key): string
    {
        $hash  = sha1($key);
        $parts = array_slice(str_split($hash, 2), 0, 2);

        return $this->path . '/' . implode('/', $parts) . '/' . $hash;
    }
}
