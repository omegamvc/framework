<?php

/**
 * Part of Omega - Environment Package.
 * php version 8.2
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Environment;

use Exception;
use InvalidArgumentException;
use Omega\Environment\Exception\MissingVariableException;

use function array_key_exists;
use function explode;
use function file;
use function file_exists;
use function is_dir;
use function is_array;
use function preg_match;
use function putenv;
use function rtrim;
use function strpos;
use function trim;

/**
 * Dotenv class.
 *
 * The `Dotenv` package is a lightweight utility for managing environment variables in PHP
 * applications. It provides a simple and efficient way to load and access environment-specific
 * configuration from a `.env` file. Unlike some well-known dotenv packages that are primarily
 * designed for development environments, this package is tailored for production use, ensuring
 * both speed and security by avoiding direct manipulation of `$_ENV` or `$_SERVER` by default.
 *
 * @category  Omega
 * @package   Environment
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */
class Dotenv
{
    /**
     * Key-value storage.
     *
     * @var array<string, string> Holds an array of the key-value storage.
     */
    protected static $variables = [];

    /**
     * Required variables.
     *
     * @var array<int, string> Holds an array of required variables.
     */
    protected static $required = [];

    /**
     * Were variables loaded?
     *
     * @var bool Determine which variables is loaded.
     */
    protected static $isLoaded = false;

    /**
     * Load and parse .env file from a given directory.
     *
     * @param string      $directoryPath Holds the path to the directory containing the .env file.
     * @param string|null $fileName      Holds the filename to load. (Optional)
     * @return void
     *
     * @throws Exception
     */
    public static function load(string $directoryPath, string $fileName = null): void
    {
        $testPath = __DIR__ . '/fixtures';

        if (is_file($directoryPath)) {
            $envFile = $directoryPath;
        } else {
            if ($testPath === $directoryPath && empty($fileName)) {
                $envFile = rtrim($directoryPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.env';
            } elseif ($testPath === $directoryPath && !empty($fileName)) {
                $envFile = rtrim($directoryPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
            } elseif ($testPath !== $directoryPath && empty($fileName)) {
                $envFile = rtrim($directoryPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.env';
            } elseif ($testPath !== $directoryPath && !empty($fileName)) {
                $envFile = rtrim($directoryPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
            } else {
                throw new \Exception('An error occurred during initialization.');
            }
        }

        if (!file_exists($envFile)) {
            throw new \Exception("File not found: $envFile");
        }

        if (is_dir($envFile)) {
            throw new \Exception("Expected file, found directory: $envFile");
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines !== false) {
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                $parts = explode('=', $line, 2);
                if (count($parts) !== 2) {
                    throw new Exception(
                        "Invalid line in .env file: $line"
                    );
                }

                list( $key, $value ) = $parts;
                $key                 = trim($key);
                $value               = trim($value);

                if (preg_match('/\s/', $key) || $key == '') {
                    throw new Exception(
                        "Invalid key in .env file: $key"
                    );
                }

                $value                 = trim($value, "\"'");
                self::$variables[$key] = $value;
            }
        } else {
            throw new Exception(
                "Unable to read the .env file: $envFile"
            );
        }

        self::$isLoaded = true;
        self::checkRequiredVariables();
    }

    /**
     * Copy all variables to putenv().
     *
     * @param string $prefix Holds the variables prefix.
     *
     * @return void
     */
    public static function copyVarsToPutenv(string $prefix = 'PHP_'): void
    {
        foreach (self::all() as $key => $value) {
            putenv("{$prefix}{$key}={$value}");
        }
    }

    /**
     * Copy all variables to $_ENV.
     *
     * @return void
     */
    public static function copyVarsToEnv(): void
    {
        foreach (self::all() as $key => $value) {
            $_ENV[$key] = $value;
        }
    }

    /**
     * Copy all variables to $_SERVER.
     *
     * @return void
     */
    public static function copyVarsToServer(): void
    {
        foreach (self::all() as $key => $value) {
            $_SERVER[$key] = $value;
        }
    }

    /**
     * Get env variables.
     *
     * @return array<string, string> Return an array with all environment variables.
     */
    public static function all(): array
    {
        return self::$variables;
    }

    /**
     * Get env variable.
     *
     * @param string $key     Holds the environment variable key to retrieve.
     * @param mixed  $default Holds the default value to return if the key is not set. Default is null.
     *
     * @return mixed Returns the value of the environment variable, or the default value if the key is not found.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$variables) && self::$variables[$key] !== '') {
            return self::$variables[$key];
        }

        return $default;
    }

    /**
     * Set an environment variable.
     *
     * @param string|array<string, string> $keys  Holds a single variable key or an array of variable keys.
     * @param mixed                        $value Holds the value for the key, or null if an array of keys is provided.
     *
     * @return void
     *
     * @throws InvalidArgumentException if the values not are a string.
     */
    public static function set(string|array $keys, mixed $value = null): void
    {
        if (is_array($keys)) {
            foreach ($keys as $k => $v) {
                if (! is_string($v)) {
                    throw new InvalidArgumentException(
                        'All values must be a string.'
                    );
                }
                self::$variables[$k] = $v;
            }
        } elseif (! is_string($value)) {
            throw new InvalidArgumentException(
                'Value must be a string.'
            );
        } else {
            self::$variables[$keys] = $value;
        }
    }

    /**
     * Set required variables.
     *
     * @param array<int, string> $variables Holds an array of variables.
     *
     * @return void
     */
    public static function setRequired(array $variables): void
    {
        self::$required = $variables;

        if (self::$isLoaded) {
            self::checkRequiredVariables();
        }
    }

    /**
     * Delete all variables.
     *
     * @return void
     */
    public static function flush(): void
    {
        self::$variables = [];
        self::$isLoaded  = false;

        foreach (array_keys($_ENV) as $key) {
            unset($_ENV[$key]);
        }
        foreach (array_keys($_SERVER) as $key) {
            unset($_SERVER[$key]);
        }
        foreach (array_keys(getenv()) as $key) {
            putenv($key);
        }
    }

    /**
     * Throw exception if any of required variables was not loaded.
     *
     * @return void
     *
     * @throws MissingVariableException if the .env variable is missing.
     */
    protected static function checkRequiredVariables(): void
    {
        foreach (self::$required as $key) {
            if (! isset(self::$variables[$key])) {
                throw new MissingVariableException(
                    ".env variable '{$key}' is missing"
                );
            }
        }
    }
}
