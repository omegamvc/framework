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
use Omega\Cache\Exceptions\DriverResolutionException;

use function is_callable;
use function sprintf;

/**
 * Class Cache
 *
 * Concrete cache manager that extends {@see AbstractCache} to provide
 * full driver registration and resolution functionality.
 *
 * This class allows registering multiple named drivers using either
 * instances or deferred closures. When a driver is accessed, it is resolved
 * on demand. If no driver is specified, the default driver is used.
 * The magic method {@see __call()} also forwards undefined method calls
 * to the active driver, making it flexible and dynamic.
 *
 * @category  Omega
 * @package   Cache
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class Cache extends AbstractCache
{
    /**
     * Initializes the cache manager with a default in-memory array driver.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultDriver(CacheInterface $driver): self
    {
        $this->defaultDriver = $driver;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDriver(string $driverName, Closure|CacheInterface $driver): self
    {
        $this->driver[$driverName] = $driver;

        return $this;
    }

    /**
     * Resolves and returns a named cache driver.
     *
     * If the driver is a closure, it will be executed and replaced with its return value.
     * If resolution fails (e.g., the closure returns null), an exception is thrown.
     *
     * @param string $driverName The name of the driver to resolve.
     * @return CacheInterface
     * @throws DriverResolutionException If the driver cannot be resolved (e.g., null returned from closure).
     */
    private function resolve(string $driverName): CacheInterface
    {
        $driver = $this->driver[$driverName];

        if (is_callable($driver)) {
            $driver = $driver();
        }

        if (null === $driver) {
            throw new DriverResolutionException(
                sprintf(
                    "Unable to resolve cache driver [%s]: null returned.",
                    $driverName
                )
            );
        }

        return $this->driver[$driverName] = $driver;
    }

    /**
     * {@inheritdoc}
     */
    public function driver(?string $driverName = null): CacheInterface
    {
        if (isset($this->driver[$driverName])) {
            return $this->resolve($driverName);
        }

        return $this->defaultDriver;
    }

    /**
     * Forwards method calls to the currently selected cache driver.
     *
     * @param string $method The method name.
     * @param array  $parameters The method parameters.
     * @return mixed The result of the method call on the selected driver.
     * @throws DriverResolutionException If the default or resolved driver is not callable.
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->driver()->{$method}(...$parameters);
    }
}
