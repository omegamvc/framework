<?php

/**
 * Part of Omega MVC - Environment Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Environment;

use Closure;
use Omega\Support\Str;

use function explode;

/**
 * Environment detector class.
 *
 * The 'EnvironmentDetector' class is designed to identify the operating environment within
 * which an application is running, distinguishing between web-based and console-based environments.
 * This class enables the application to adapt its behavior accordingly, allowing for seamless
 * interaction and functionality across different platforms. By accurately detecting the environment,
 * the class facilitates the execution of context-specific actions, ensuring optimal performance and
 * user experience across various deployment scenarios.
 *
 * @category  Omega
 * @package   Environment
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */
class EnvironmentDetector
{
    /**
     * Detect the application's current environment.
     *
     * @param Closure            $callback    Holds the callback to use for detecting environment.
     * @param array<string>|null $consoleArgs Holds an array of console arguments or null.
     *
     * @return string Return the application's current environment.
     */
    public function detect(Closure $callback, ?array $consoleArgs = null): string
    {
        if ($consoleArgs) {
            return $this->detectConsoleEnvironment($callback, $consoleArgs);
        }

        return $this->detectWebEnvironment($callback);
    }

    /**
     * Set the application environment for a web request.
     *
     * @param Closure $callback Holds the callback to use for detecting environment.
     *
     * @return string Return the application's current environment.
     */
    protected function detectWebEnvironment(Closure $callback): string
    {
        return $callback();
    }

    /**
     * Set the application environment from command-line arguments.
     *
     * @param Closure       $callback Holds the callback to use for detecting environment.
     * @param array<string> $args     Holds an array of arguments for the environment.
     *
     * @return string Return the application's environment.
     */
    protected function detectConsoleEnvironment(Closure $callback, array $args = []): string
    {
        if (! is_null($value = $this->getEnvironmentArgument($args))) {
            return $value;
        }

        return $this->detectWebEnvironment($callback);
    }

    /**
     * Get the environment argument from the console.
     *
     * @param array<string> $args Holds an array of argument.
     *
     * @return ?string Return the environment argument from the console.
     */
    protected function getEnvironmentArgument(array $args): ?string
    {
        foreach ($args as $i => $value) {
            if ($value === '--env') {
                return $args[$i + 1] ?? null;
            }

            if (Str::startsWith($value, '--env')) {
                $explodedValue = explode('=', $value);

                return $explodedValue[1] ?? null;
            }
        }

        return null;
    }
}
