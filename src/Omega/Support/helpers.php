<?php

/**
 * Part of Omega -  Helpers Package.
 * php version 8,2
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

use Omega\Application\Application;
use Omega\Environment\Dotenv;

/**
 * Omega Helpers Functions.
 *
 * This file contains various helper functions to assist in development. It includes
 * path manipulation, operating system detection, response handling, view rendering,
 * configuration access, environment variable retrieval, application instance retrieval,
 * debugging aids, and CSRF protection.
 *
 * @category   Omega
 * @package    Helpers
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
if (! function_exists('app')) {
    /**
     * Get an instance of the Omega Application.
     *
     * @param string|null $alias Holdds the instance alias or null to get the main application instance.
     * @return mixed Returns the resolved instance or the main application instance if no alias is provided.
     */
    function app(?string $alias = null): mixed
    {
        if (is_null($alias)) {
            return Application::getInstance();
        }

        return Application::getInstance()->resolve($alias);
    }
}

if (! function_exists('config')) {
    /**
     * Alias or set a configuration value.
     *
     * @param string|null $key     Holds the configuration key or null to get the entire configuration.
     * @param mixed       $default Holds the default value if the key is not found.
     * @return mixed Returns the configuration value or the entire configuration if no key is provided.
     */
    function config(?string $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return app('config');
        }

        return app('config')->get($key, $default);
    }
}

if (! function_exists('csrf')) {
    /**
     * Set the CSRF token.
     *
     * Generates a CSRF token and stores it in the session.
     *
     * @return string Returns the generated CSRF token.
     * @throws Exception if session is not enabled.
     */
    function csrf(): string
    {
        $session = session();

        if (! $session) {
            throw new Exception(
                'Session is not enabled.'
            );
        }

        $session->put('token', $token = bin2hex(random_bytes(32)));

        return $token;
    }
}

if (! function_exists('dd')) {
    /**
     * Dump variables and end script execution.
     *
     * @param mixed ...$params The variables to be dumped.
     * @return void
     */
    function dd(...$params): void
    {
        var_dump(...$params);

        die;
    }
}

if (! function_exists('dump')) {
    /**
     * Display a variable dump in a formatted manner.
     *
     * @param array<mixed> $array The array to be dumped.
     * @return void
     */
    function dump(array $array): void
    {
        echo '<pre style="background-color:#f4f4f4; padding: 10px; border-radius: 5px; border: 1px solid #ccc;
                           font-family: Arial, sans-serif; font-size: 14px; color: #333">';
        print_r($array);
        echo '</pre>';
    }
}

if (! function_exists('env')) {
    /**
     * Get the value of an environment variable.
     *
     * @param string $key     Holds the key of the environment variable.
     * @param mixed  $default Holds the default value if the key is not set.
     * @return mixed Returns the value of the environment variable or the default value if the key is not set.
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = Dotenv::get($key);

        if ($value === null) {
            return value($default);
        }

        $lower = strtolower($value);

        return match ($lower) {
            'true', '(true)'     => true,
            'false', '(false)'   => false,
            'empty', '(empty)'   => '',
            'null', '(null)'     => null,
            default              => $value,
        };
    }

    function value(mixed $value): mixed
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (! function_exists('get_operating_system')) {
    /**
     * Get the operating system name.
     *
     * Retrieves the operatingsystem name (e.g. `mac`, `windows`, `linux` or `unknown`).
     *
     * @return string Returns the operating system name (e.g., "mac", "windows", "linux", or "unknown").
     */
    function get_operating_system(): string
    {
        $os = strtolower(PHP_OS_FAMILY);

        switch ($os) {
            case 'darwin':
                return 'mac';
            case 'win':
                return 'windows';
            case 'linux':
                return 'linux';
            default:
                return 'unknown';
        }
    }
}

if (! function_exists('head')) {
    /**
     * Get the first element of an array.
     *
     * @param array<mixed> $array Holds the array to get the first element.
     * @return mixed
     */
    function head(array $array): mixed
    {
        return reset($array);
    }
}

if (! function_exists('now')) {
    /**
     * Returns the current date and time in the configured timezone.
     *
     * @return string Return the formatted time zone.
     */
    function now(): string
    {
        return app()->setCurrentTimeZone()->getCurrentTimeZone();
    }
}

if (! function_exists('redirect')) {
    /**
     * Redirect to a specific URL.
     *
     * Redirects to a specified URL and return the result of the redirect
     * session if no key is provided.
     *
     * @param string $url Holds the URL to redirect to.
     * @return mixed Return that result of the redirect operation.
     */
    function redirect(string $url): mixed
    {
        return response()->redirect($url);
    }
}

if (! function_exists('response')) {
    /**
     * Get the response instance.
     *
     * @return mixed Returns the response instance.
     */
    function response(): mixed
    {
        return app('response');
    }
}

if (! function_exists('secure')) {
    /**
     * Secure the CSRF token.
     *
     * Compares the CSRF token from the session with the one submitted in the POST data.
     *
     * @return void
     * @throws Exception if session is not enabled or CSRF token mismatch.
     */
    function secure(): void
    {
        $session = session();

        if (! $session) {
            throw new Exception(
                'Session is not enabled.'
            );
        }

        if (
            !isset($_POST['csrf'])
            || ! $session->has('token')
            || ! hash_equals($session->get('token'), $_POST['csrf'])
        ) {
            throw new Exception(
                'CSRF token mismatch'
            );
        }
    }
}

if (! function_exists('session')) {
    /**
     * Get or set a session value.
     *
     * @param string|null $key     Holds the session key or null to get the entire session.
     * @param mixed       $default Holds the default value if the key is not found.
     * @return mixed Returns the session value or the entire session if no key is provided.
     */
    function session(?string $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return app('session');
        }

        return app('session')->get($key, $default);
    }
}

if (! function_exists('validate')) {
    /**
     * Validate input.
     *
     * Validates input data against specified rules.
     *
     * @param array<string, mixed> $data        Holds an array of data to validate.
     * @param array<string, mixed> $rules       Holds an array of rules.
     * @param string               $sessionName Holds the session name for storing validation errors.
     * @return mixed Returns the validation result.
     */
    function validate(array $data, array $rules, string $sessionName = 'errors'): mixed
    {
        return app('validator')->validate($data, $rules, $sessionName);
    }
}

if (! function_exists('value')) {
    /**
     * The default value of the given value.
     *
     * @param mixed $value   Holds the value to check.
     * @param mixed ...$args Holds additional arguments if `$values` is a Closure.
     * @return mixed Returns the default value or the result of the Closure if `$value` is a Closure.
     */
    function value(mixed $value, mixed ...$args): mixed
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}
