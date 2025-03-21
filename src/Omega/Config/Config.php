<?php

/**
 * Part of Omega - Config Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Config;

use Omega\Utils\Path;

use function array_shift;
use function explode;
use function file_exists;

/**
 * Config class.
 *
 * The `Config` class provides a simple and efficient way to access configuration
 * parameters stored in PHP files within the `config` directory of your application.
 *
 * @category  Omega
 * @package   Config
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */
class Config
{
    /**
     * Loaded params.
     *
     * @var array<string, array<string, mixed>> Holds an array of loaded parameters.
     */
    private array $loaded = [];

    /**
     * Get the config parameter.
     *
     * @param string $key     Holds the config key, which may include dots for nested values.
     * @param mixed  $default Holds the default value to return if the key is not found.
     * @return mixed Return the value of the configuration parameter, or the default value if not found.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $file     = array_shift($segments);

        if (!isset($this->loaded[$file])) {
            $this->loaded[$file] = $this->loadConfigFile(Path::getPath('config', $file . '.php'));
        }

        return $this->withDots($this->loaded[$file], $segments) ?? $default;
    }

    /**
     * Retrieve all configuration parameters.
     *
     * @return array<string, mixed> The entire configuration array.
     */
    public function all(): array
    {
        return $this->loaded;
    }

    /**
     * Determine whether a configuration key exists.
     *
     * @param string $key The key to check.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool
    {
        return $this->get($key, '__not_found__') !== '__not_found__';
    }

    /**
     * Set a configuration value.
     *
     * @param string $key   The configuration key.
     * @param mixed  $value The value to set.
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $file     = array_shift($segments);

        if (!isset($this->loaded[$file])) {
            $this->loaded[$file] = $this->loadConfigFile(Path::getPath('config', $file . '.php'));
        }

        $this->setByDots($this->loaded[$file], $segments, $value);
    }

    /**
     * Remove a configuration key.
     *
     * @param string $key The key to remove.
     * @return void
     */
    public function remove(string $key): void
    {
        $segments = explode('.', $key);
        $file     = array_shift($segments);

        if (!isset($this->loaded[$file])) {
            return;
        }

        $this->unsetByDots($this->loaded[$file], $segments);
    }

    /**
     * Clears all configuration data.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->loaded = [];
    }

    /**
     * Retrieve config key using dot notation.
     *
     * @param array<string, mixed> $array    Holds an array of key.
     * @param array<int, string>   $segments Holds an array of arguments.
     * @return mixed Return the value or null if not found.
     */
    private function withDots(array $array, array $segments): mixed
    {
        foreach ($segments as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return null;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Set a config value using dot notation.
     *
     * @param array<string, mixed> $array    Holds an array reference.
     * @param array<int, string>   $segments Holds an array of keys.
     * @param mixed                $value    The value to set.
     * @return void
     */
    private function setByDots(array &$array, array $segments, mixed $value): void
    {
        while (count($segments) > 1) {
            $segment = array_shift($segments);
            if (!isset($array[$segment]) || !is_array($array[$segment])) {
                $array[$segment] = [];
            }
            $array = &$array[$segment];
        }

        $array[array_shift($segments)] = $value;
    }

    /**
     * Unset a config value using dot notation.
     *
     * @param array<string, mixed> $array    Holds an array reference.
     * @param array<int, string>   $segments Holds an array of keys.
     * @return void
     */
    private function unsetByDots(array &$array, array $segments): void
    {
        while (count($segments) > 1) {
            $segment = array_shift($segments);
            if (!isset($array[$segment]) || !is_array($array[$segment])) {
                return;
            }
            $array = &$array[$segment];
        }

        unset($array[array_shift($segments)]);
    }

    /**
     * Load the configuration file.
     *
     * @param string $configFile Holds the configuration file name.
     * @return array<string, mixed> Return an array containing the configuration parameters.
     */
    private function loadConfigFile(string $configFile): array
    {
        return file_exists($configFile) ? (array)require $configFile : [];
    }
}
