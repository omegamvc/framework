<?php

/**
 * Part of Omega - Security Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Security\Hashing;

use function array_key_exists;

/**
 * HashManager handles the registration and resolution of multiple hashing drivers.
 *
 * It acts as a central interface for managing different hashing strategies,
 * such as bcrypt, Argon2, or custom implementations. It allows setting a default
 * hashing driver and retrieving specific drivers by name.
 *
 * This class implements the HashInterface and can be used directly
 * or injected where hashing logic is required.
 *
 * @category   Omega
 * @package    Security
 * @subpackage Hashing
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class HashManager implements HashInterface
{
    /**
     * A collection of named hashing drivers.
     *
     * The array holds instances of classes that implement HashInterface, each
     * associated with a unique string identifier.
     *
     * @var array<string, HashInterface>
     */
    private array $driver = [];

    /**
     * The default hashing driver used when no specific driver is requested.
     *
     * This driver is returned from `driver()` if no matching name is found.
     */
    private HashInterface $defaultDriver;

    /**
     * Create a new HashManager instance with a default hasher.
     *
     * By default, it registers the DefaultHasher as the fallback hashing strategy.
     */
    public function __construct()
    {
        $this->setDefaultDriver(new DefaultHasher());
    }

    /**
     * Set the default hashing driver.
     *
     * @param HashInterface $driver The driver to use as default.
     * @return $this Fluent instance for method chaining.
     */
    public function setDefaultDriver(HashInterface $driver): self
    {
        $this->defaultDriver = $driver;

        return $this;
    }

    /**
     * Register a new hashing driver by name.
     *
     * @param string $driverName Unique identifier for the driver.
     * @param HashInterface $driver The driver implementation.
     * @return $this Fluent instance for method chaining.
     */
    public function setDriver(string $driverName, HashInterface $driver): self
    {
        $this->driver[$driverName] = $driver;

        return $this;
    }

    /**
     * Resolve and retrieve a hashing driver.
     *
     * If a name is provided and the driver is registered, it returns the corresponding driver.
     * Otherwise, it returns the default driver.
     *
     * @param string|null $driver The name of the driver to resolve (optional).
     * @return HashInterface The resolved hashing driver.
     */
    public function driver(?string $driver = null): HashInterface
    {
        if (array_key_exists($driver, $this->driver)) {
            return $this->driver[$driver];
        }

        return $this->defaultDriver;
    }

    /**
     * {@inheritdoc}
     */
    public function info(string $hash): array
    {
        return $this->driver()->info($hash);
    }

    /**
     * {@inheritdoc}
     */
    public function make(string $value, array $options = []): string
    {
        return $this->driver()->make($value, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function verify(string $value, string $hashedValue, array $options = []): bool
    {
        return $this->driver()->verify($value, $hashedValue, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function isValidAlgorithm(string $hash): bool
    {
        return $this->driver()->isValidAlgorithm($hash);
    }
}
