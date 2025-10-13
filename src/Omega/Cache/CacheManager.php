<?php

declare(strict_types=1);

namespace Omega\Cache;

use Closure;
use DateInterval;
use Omega\Cache\Exceptions\UnknownStorageDriverException;
use Omega\Cache\Storage\ArrayStorage;

use function is_callable;
use function sprintf;


class CacheManager implements CacheInterface
{
    /** @var array<string, CacheInterface|Closure(): CacheInterface> */
    private array $driver = [];

    /** @var CacheInterface */
    private CacheInterface $defaultDriver;

    /**
     *
     */
    public function __construct()
    {
        $this->setDefaultDriver(new ArrayStorage());
    }

    /**
     * @param CacheInterface $driver
     * @return $this
     */
    public function setDefaultDriver(CacheInterface $driver): self
    {
        $this->defaultDriver = $driver;

        return $this;
    }

    /**
     * @param string                                   $driverName
     * @param Closure(): CacheInterface|CacheInterface $driver
     * @return self
     */
    public function setDriver(string $driverName, Closure|CacheInterface $driver): self
    {
        $this->driver[$driverName] = $driver;

        return $this;
    }

    /**
     * @param string $driverName
     * @return CacheInterface
     * @throws UnknownStorageDriverException
     */
    private function resolve(string $driverName): CacheInterface
    {
        $driver = $this->driver[$driverName];

        if (is_callable($driver)) {
            $driver = $driver();
        }

        if (null === $driver) {
            throw new UnknownStorageDriverException($driverName);
        }

        return $this->driver[$driverName] = $driver;
    }

    /**
     * @param string|null $driver_name
     * @return CacheInterface
     * @throws UnknownStorageDriverException
     */
    public function driver(?string $driver_name = null): CacheInterface
    {
        if (isset($this->driver[$driver_name])) {
            return $this->resolve($driver_name);
        }

        return $this->defaultDriver;
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws UnknownStorageDriverException
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->driver()->{$method}(...$parameters);
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     * @throws UnknownStorageDriverException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->driver()->get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int|DateInterval|null $ttl
     * @return bool
     * @throws UnknownStorageDriverException
     */
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool
    {
        return $this->driver()->set($key, $value, $ttl);
    }

    /**
     * @param string $key
     * @return bool
     * @throws UnknownStorageDriverException
     */
    public function delete(string $key): bool
    {
        return $this->driver()->delete($key);
    }

    /**
     * @return bool
     * @throws UnknownStorageDriverException
     */
    public function clear(): bool
    {
        return $this->driver()->clear();
    }

    /**
     * @param iterable $keys
     * @param mixed|null $default
     * @return iterable
     * @throws UnknownStorageDriverException
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return $this->driver()->getMultiple($keys, $default);
    }

    /**
     * @param iterable $values
     * @param int|DateInterval|null $ttl
     * @return bool
     * @throws UnknownStorageDriverException
     */
    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool
    {
        return $this->driver()->setMultiple($values, $ttl);
    }

    /**
     * @param iterable $keys
     * @return bool
     * @throws UnknownStorageDriverException
     */
    public function deleteMultiple(iterable $keys): bool
    {
        return $this->driver()->deleteMultiple($keys);
    }

    /**
     * @param string $key
     * @return bool
     * @throws UnknownStorageDriverException
     */
    public function has(string $key): bool
    {
        return $this->driver()->has($key);
    }

    /**
     * @param string $key
     * @param int $value
     * @return int
     * @throws UnknownStorageDriverException
     */
    public function increment(string $key, int $value): int
    {
        return $this->driver()->increment($key, $value);
    }

    /**
     * @param string $key
     * @param int $value
     * @return int
     * @throws UnknownStorageDriverException
     */
    public function decrement(string $key, int $value): int
    {
        return $this->driver()->decrement($key, $value);
    }

    /**
     * @param string $key
     * @param Closure $callback
     * @param int|DateInterval|null $ttl
     * @return mixed
     * @throws UnknownStorageDriverException
     */
    public function remember(string $key, Closure $callback, int|DateInterval|null $ttl): mixed
    {
        return $this->driver()->remember($key, $callback, $ttl);
    }
}
