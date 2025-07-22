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

namespace Omega\Cache;

use Closure;
use DateInterval;
use Omega\Cache\Exceptions\DriverResolutionException;
use Omega\Cache\Storage\ArrayStorage;

/**
 * Class AbstractCache
 *
 * Abstract base class for cache managers supporting multiple drivers.
 *
 * This class provides a partial implementation of the {@see CacheInterface},
 * delegating core cache operations to a default or named driver. It defines
 * an internal structure to manage and forward calls to cache drivers, but
 * leaves the responsibility of registering and resolving drivers to
 * concrete subclasses.
 *
 * @category  Omega
 * @package   Cache
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @property array<string, CacheInterface|Closure(): CacheInterface> $driver
 *           The list of cache drivers, either instances or deferred closures.
 * @property CacheInterface $defaultDriver
 *           The default driver to use when no specific one is requested.
 *
 * @see CacheInterface
 * @see Cache
 */
abstract class AbstractCache implements CacheInterface
{
    /** @var array<string, CacheInterface|Closure(): CacheInterface> Registry of cache drivers, keyed by name. */
    protected array $driver = [];

    /** @var CacheInterface The default cache driver used when no specific driver is requested. */
    protected CacheInterface $defaultDriver;

    /**
     * Initializes the cache manager with a default in-memory array driver.
     *
     * @return void
     */
    public function __construct()
    {
        $this->setDefaultDriver(new ArrayStorage());
    }

    /**
     * Sets the default driver to be used when no named driver is specified.
     *
     * @param CacheInterface $driver The cache driver to use by default.
     * @return self
     */
    abstract public function setDefaultDriver(CacheInterface $driver): self;

    /**
     * Registers a named cache driver, either as a direct instance or a deferred closure.
     *
     * @param string                                    $driverName The name of the driver.
     * @param Closure(): CacheInterface|CacheInterface $driver     A driver instance or a closure returning one.
     * @return self
     */
    abstract public function setDriver(string $driverName, Closure|CacheInterface $driver): self;

    /**
     * Retrieves a specific driver by name, or returns the default driver.
     *
     * @param string|null $driverName The name of the driver, or null to use the default.
     * @return CacheInterface
     * @throws DriverResolutionException If the named driver cannot be resolved.
     */
    abstract public function driver(?string $driverName = null): CacheInterface;

    /**
     * {@inheritdoc}
     *
     * @throws DriverResolutionException If the named driver cannot be resolved.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->driver()->get($key, $default);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DriverResolutionException If the named driver cannot be resolved.
     */
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool
    {
        return $this->driver()->set($key, $value, $ttl);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DriverResolutionException If the named driver cannot be resolved.
     */
    public function delete(string $key): bool
    {
        return $this->driver()->delete($key);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DriverResolutionException If the named driver cannot be resolved.
     */
    public function clear(): bool
    {
        return $this->driver()->clear();
    }

    /**
     * {@inheritdoc}
     *
     * @throws DriverResolutionException If the named driver cannot be resolved.
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return $this->driver()->getMultiple($keys, $default);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DriverResolutionException If the named driver cannot be resolved.
     */
    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool
    {
        return $this->driver()->setMultiple($values, $ttl);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DriverResolutionException If the named driver cannot be resolved.
     */
    public function deleteMultiple(iterable $keys): bool
    {
        return $this->driver()->deleteMultiple($keys);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DriverResolutionException If the named driver cannot be resolved.
     */
    public function has(string $key): bool
    {
        return $this->driver()->has($key);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DriverResolutionException If the named driver cannot be resolved.
     */
    public function increment(string $key, int $value): int
    {
        return $this->driver()->increment($key, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DriverResolutionException If the named driver cannot be resolved.
     */
    public function decrement(string $key, int $value): int
    {
        return $this->driver()->decrement($key, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DriverResolutionException If the named driver cannot be resolved.
     */
    public function remember(string $key, Closure $callback, int|DateInterval|null $ttl = null): mixed
    {
        return $this->driver()->remember($key, $callback, $ttl);
    }
}
