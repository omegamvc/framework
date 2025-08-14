<?php

declare(strict_types=1);

namespace Omega\Cache\Storage;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

use function array_key_exists;
use function time;

class ArrayStorage extends AbstractStorage
{
    use StorageTrait;

    /**
     * @var array<string, array{value: mixed, timestamp?: int, mtime?: float}>
     */
    protected array $storage = [];

    /**
     * @param int $defaultTTL
     */
    public function __construct(private readonly int $defaultTTL = 3_600)
    {
    }

    /**
     * Get info of storage.
     *
     * @param string $key
     * @return array<string, array{value: mixed, timestamp?: int, mtime?: float}>
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

        return (new DateTimeImmutable())->add(new DateInterval("PT{$ttl}S"))->getTimestamp();
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    private function isExpired(int $timestamp): bool
    {
        return $timestamp !== 0 && time() >= $timestamp;
    }
}
