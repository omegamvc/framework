<?php

/**
 * Part of Omega - Config Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Config;

use ArrayAccess;

use function array_key_exists;

/**
 * ConfigRepository class manages configuration settings stored in an array.
 *
 * Implements ArrayAccess to allow array-like access to configuration values.
 *
 * @category  Omega
 * @package   Config
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @implements ArrayAccess<string, mixed>
 */
class ConfigRepository implements ArrayAccess
{
    /**
     * Create a new ConfigRepository instance with an optional initial config array.
     *
     * @param array<string, mixed> $config Initial configuration key-value pairs.
     * @return void
     */
    public function __construct(protected array $config = [])
    {
    }

    /**
     * Determine if the given configuration key exists.
     *
     * @param string $key Configuration key to check.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->config);
    }

    /**
     * Retrieve a configuration value by key.
     *
     * @param string $key Configuration key to retrieve.
     * @param mixed $default Default value to return if key does not exist.
     * @return mixed The configuration value or default if key not found.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Set or update a configuration value by key.
     *
     * @param string $key Configuration key to set.
     * @param mixed $value Value to set for the given key.
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    /**
     * Append a value to an array configd at the given configuration key.
     *
     * If the key does not exist, it will be created as an array.
     *
     * @param string $key Configuration key for the array.
     * @param mixed $value Value to append to the array.
     * @return void
     */
    public function push(string $key, mixed $value): void
    {
        $array   = $this->get($key, []);
        $array[] = $value;
        $this->set($key, $array);
    }

    /**
     * Convert the entire configuration store back to an array.
     *
     * @return array<string, mixed> The full configuration array.
     */
    public function toArray(): array
    {
        return $this->config;
    }

    /**
     * Check if a configuration offset exists (ArrayAccess).
     *
     * @param mixed $offset Configuration key to check.
     * @return bool True if key exists, false otherwise.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Get a configuration value by offset (ArrayAccess).
     *
     * @param mixed $offset Configuration key to retrieve.
     * @return mixed The configuration value or null if not found.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Set a configuration value by offset (ArrayAccess).
     *
     * @param mixed $offset Configuration key to set.
     * @param mixed $value Value to set.
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Unset a configuration value or set it to null by offset (ArrayAccess).
     *
     * @param mixed $offset Configuration key to unset.
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->set($offset, null);
    }
}
