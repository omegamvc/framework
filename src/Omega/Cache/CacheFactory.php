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

namespace Omega\Cache;

use Closure;
use DateInterval;
use Omega\Cache\Exceptions\UnknownStorageException;
use Omega\Cache\Storage\File;

use function is_callable;

/**
 * Class CacheManager
 *
 * The CacheManager acts as a central point of access for all cache storage drivers.
 * It allows setting and retrieving multiple cache drivers (e.g. file, memory, Redis),
 * and automatically delegates cache operations to the default driver if no specific
 * driver is requested.
 *
 * @category  Omega
 * @package   Cache
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class CacheFactory implements CacheInterface
{
    /**
     * Registered cache drivers.
     *
     * Each driver can be a direct instance of {@see CacheInterface} or a lazy-loaded
     * closure returning a cache instance.
     *
     * @var array<string, CacheInterface|Closure(): CacheInterface>
     */
    private array $driver = [];

    /** @var CacheInterface The default cache driver used when no specific driver name is provided. */
    private CacheInterface $defaultDriver;

    /**
     * Creates a new CacheManager instance and initializes the default cache driver.
     *
     * The constructor always instantiates a {@see File} driver using the
     * provided configuration options. This ensures that a file-based cache is
     * available for essential framework operations (such as view caching),
     * even when another cache driver is selected as the active default.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Sets the default cache driver instance.
     *
     * @param CacheInterface $driver The cache driver to be used as default.
     * @return $this Returns the current instance for method chaining.
     */
    public function setDefaultDriver(CacheInterface $driver): self
    {
        $this->defaultDriver = $driver;

        return $this;
    }

    /**
     * Registers a named cache driver.
     *
     * Drivers can be added either as ready-to-use instances or as closures
     * that return a {@see CacheInterface} instance upon resolution.
     *
     * @param string                                   $driverName The unique driver name.
     * @param Closure(): CacheInterface|CacheInterface $driver     The driver instance or a closure returning it.
     * @return self Returns the current instance for method chaining.
     */
    public function setDriver(string $driverName, Closure|CacheInterface $driver): self
    {
        $this->driver[$driverName] = $driver;

        return $this;
    }

    /**
     * Resolves a cache driver by its registered name.
     *
     * If the driver is registered as a closure, it will be executed and its
     * resulting instance cached for future use.
     *
     * @param string $driverName The name of the driver to resolve.
     * @return CacheInterface The resolved cache driver instance.
     * @throws UnknownStorageException if a requested cache storage driver is unknown, unregistered, or unsupported.
     */
    private function resolve(string $driverName): CacheInterface
    {
        $driver = $this->driver[$driverName];

        if (is_callable($driver)) {
            $driver = $driver();
        }

        if (null === $driver) {
            throw new UnknownStorageException($driverName);
        }

        return $this->driver[$driverName] = $driver;
    }

    /**
     * Retrieves a cache driver by name or returns the default driver if none is provided.
     *
     * @param string|null $driver_name Optional name of the driver to use.
     * @return CacheInterface The corresponding cache driver instance.
     * @throws UnknownStorageException if a requested cache storage driver is unknown, unregistered, or unsupported.
     */
    public function getDriver(?string $driver_name = null): CacheInterface
    {
        if (isset($this->driver[$driver_name])) {
            return $this->resolve($driver_name);
        }

        return $this->defaultDriver;
    }

    /**
     * Magic method to delegate cache operations to the default driver.
     *
     * This allows direct method calls (e.g., `$cache->get('key')`) on the manager
     * without explicitly calling `driver()`.
     *
     * @param string $method The method name being called.
     * @param array  $parameters The parameters passed to the method.
     * @return mixed The result returned by the underlying cache driver.
     * @throws UnknownStorageException if a requested cache storage driver is unknown, unregistered, or unsupported.
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->getDriver()->{$method}(...$parameters);
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownStorageException if a requested cache storage driver is unknown, unregistered, or unsupported.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getDriver()->get($key, $default);
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownStorageException
     */
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool
    {
        return $this->getDriver()->set($key, $value, $ttl);
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownStorageException if a requested cache storage driver is unknown, unregistered, or unsupported.
     */
    public function delete(string $key): bool
    {
        return $this->getDriver()->delete($key);
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownStorageException if a requested cache storage driver is unknown, unregistered, or unsupported.
     */
    public function clear(): bool
    {
        return $this->getDriver()->clear();
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownStorageException if a requested cache storage driver is unknown, unregistered, or unsupported.
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return $this->getDriver()->getMultiple($keys, $default);
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownStorageException if a requested cache storage driver is unknown, unregistered, or unsupported.
     */
    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool
    {
        return $this->getDriver()->setMultiple($values, $ttl);
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownStorageException if a requested cache storage driver is unknown, unregistered, or unsupported.
     */
    public function deleteMultiple(iterable $keys): bool
    {
        return $this->getDriver()->deleteMultiple($keys);
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownStorageException if a requested cache storage driver is unknown, unregistered, or unsupported.
     */
    public function has(string $key): bool
    {
        return $this->getDriver()->has($key);
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownStorageException if a requested cache storage driver is unknown, unregistered, or unsupported.
     */
    public function increment(string $key, int $value): int
    {
        return $this->getDriver()->increment($key, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownStorageException if a requested cache storage driver is unknown, unregistered, or unsupported.
     */
    public function decrement(string $key, int $value): int
    {
        return $this->getDriver()->decrement($key, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownStorageException if a requested cache storage driver is unknown, unregistered, or unsupported.
     */
    public function remember(string $key, Closure $callback, int|DateInterval|null $ttl): mixed
    {
        return $this->getDriver()->remember($key, $callback, $ttl);
    }
}
