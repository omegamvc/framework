<?php

declare(strict_types=1);

namespace Omega\Cache\Storage;

use Closure;
use DateInterval;
use Omega\Cache\CacheInterface;

abstract class AbstractStorage implements CacheInterface
{
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

            $state = null === $state ? $result : $result && $state;
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
