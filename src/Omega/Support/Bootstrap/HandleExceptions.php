<?php

/**
 * Part of Omega - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Bootstrap;

use ErrorException;
use Omega\Application\Application;
use Omega\Exceptions\ExceptionHandler;
use Throwable;

use function error_get_last;
use function error_reporting;
use function getenv;
use function in_array;
use function ini_set;
use function php_sapi_name;
use function register_shutdown_function;
use function set_error_handler;
use function set_exception_handler;
use function str_repeat;

use const E_COMPILE_ERROR;
use const E_CORE_ERROR;
use const E_DEPRECATED;
use const E_ERROR;
use const E_PARSE;
use const E_USER_DEPRECATED;

/**
 * Class HandleExceptions
 *
 * Handles the registration and management of error and exception handlers
 * for the application lifecycle. This includes:
 * - Converting PHP errors into exceptions
 * - Handling uncaught exceptions
 * - Handling fatal errors on shutdown
 * - Logging deprecation notices
 *
 * It reserves memory to ensure error handling works even under memory exhaustion,
 * and uses the registered exception handler to report and render exceptions.
 *
 * @category   Omega
 * @package    Support
 * @subpackage Bootstrap
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
 * @todo: This class should be redesigned to rely on the Logger from Omega 1.0.0.
 * @todo: NOTE: The Omega 1.0.0 Logger has not been tested yet.
 */
class HandleExceptions
{
    /** @var Application The application instance. */
    private Application $app;

    /** @var string|null Reserved memory to ensure exception handling during out-of-memory errors. */
    public static ?string $reserveMemory = null;

    /**
     * Bootstraps the application's error and exception handling.
     *
     * Sets error reporting to `E_ALL`, registers custom error, exception, and shutdown
     * handlers unless the application is running in a testing environment. Also turns off
     * error display in non-testing environments.
     *
     * @param Application $app The application instance.
     * @return void
     */
    public function bootstrap(Application $app): void
    {
        self::$reserveMemory = str_repeat('x', 32_768);

        $this->app = $app;

        error_reporting(E_ALL);

        if (getenv('APP_ENV') !== 'testing') {
            set_error_handler([$this, 'handleError']);
            set_exception_handler([$this, 'handleException']);
        }

        register_shutdown_function([$this, 'handleShutdown']);

        if (getenv('APP_ENV') !== 'testing') {
            ini_set('display_errors', 'Off');
        }
    }

    /**
     * Converts PHP errors to ErrorException instances based on the error reporting level.
     *
     * Deprecation warnings are handled separately via `handleDeprecationError`.
     *
     * @param int $level The error level (e.g. E_WARNING, E_NOTICE).
     * @param string $message The error message.
     * @param string $file The file where the error occurred.
     * @param int|null $line The line number where the error occurred.
     * @return void
     * @throws ErrorException If the error level is not filtered out by error_reporting().
     */
    public function handleError(int $level, string $message, string $file = '', ?int $line = 0): void
    {
        if ($this->isDeprecation($level)) {
            $this->handleDeprecationError($message, $file, $line, $level);
        }

        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handles deprecation warnings by logging them.
     *
     * @param string $message The deprecation message.
     * @param string $file The file where the deprecation occurred.
     * @param int $line The line number of the deprecation.
     * @param int $level The error level (typically E_DEPRECATED or E_USER_DEPRECATED).
     * @return void
     */
    private function handleDeprecationError(string $message, string $file, int $line, int $level): void
    {
        $this->log($level, $message);
    }

    /**
     * Handles uncaught exceptions.
     *
     * Clears the reserved memory, reports the exception via the registered handler,
     * and renders it if the application is not running in CLI mode.
     *
     * @param Throwable $th The uncaught exception or error.
     * @return void
     * @throws Throwable If rendering or reporting fails.
     */
    public function handleException(Throwable $th): void
    {
        self::$reserveMemory = null;

        $handler = $this->getHandler();
        $handler->report($th);
        if (php_sapi_name() !== 'cli') {
            $handler->render($this->app['request'], $th)->send();
        }
    }

    /**
     * Handles fatal errors during script shutdown.
     *
     * Checks if the last error is fatal, and if so, delegates to `handleException`.
     * Releases reserved memory to ensure graceful shutdown.
     *
     * @return void
     * @throws Throwable If a fatal error is found and cannot be handled.
     */
    public function handleShutdown(): void
    {
        self::$reserveMemory = null;
        $error               = error_get_last();
        if ($error && $this->isFatal($error['type'])) {
            $this->handleException(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
        }
    }

    /**
     * Logs a message with the given severity level, if a logger is available.
     *
     * @param int $level The log level (e.g. E_WARNING, E_NOTICE).
     * @param string $message The message to log.
     * @return bool True if the message was logged, false otherwise.
     */
    private function log(int $level, string $message): bool
    {
        if ($this->app->has('log')) {
            $this->app['log']->log($level, $message);

            return true;
        }

        return false;
    }

    /**
     * Retrieves the application's registered exception handler.
     *
     * @return ExceptionHandler The exception handler instance.
     */
    private function getHandler(): ExceptionHandler
    {
        return $this->app[ExceptionHandler::class];
    }

    /**
     * Determines if the error level corresponds to a deprecation warning.
     *
     * @param int $level The error level.
     * @return bool True if it's a deprecation warning, false otherwise.
     */
    private function isDeprecation(int $level): bool
    {
        return in_array($level, [E_DEPRECATED, E_USER_DEPRECATED]);
    }

    /**
     * Determines if the error level corresponds to a fatal error.
     *
     * @param int $level The error level.
     * @return bool True if it's a fatal error, false otherwise.
     */
    private function isFatal(int $level): bool
    {
        return in_array($level, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }
}
