<?php

declare(strict_types=1);

namespace Omega\Support\Bootstrap;

use ErrorException;
use Omega\Application\Application;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Exceptions\ExceptionHandler;
use Throwable;

use function error_get_last;
use function error_reporting;
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

class HandleExceptions
{
    private Application $app;
    public static ?string $reserveMemory = null;

    /**
     * @throws InvalidDefinitionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function bootstrap(Application $app): void
    {
        self::$reserveMemory = str_repeat('x', 32_768);

        $this->app = $app;

        error_reporting(E_ALL);

        /** @phpstan-ignore-next-line */
        if ('testing' !== $app->getEnvironment()) {
	        set_error_handler([$this, 'handleError']);

	        set_exception_handler([$this, 'handleException']);
		}
		
        register_shutdown_function([$this, 'handleShutdown']);

        if ('testing' !== $app->getEnvironment()) {
            ini_set('display_errors', 'Off');
        }
    }

    /**
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int|null $line
     * @return void
     * @throws ErrorException
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
     * @param string $message
     * @param string $file
     * @param int $line
     * @param int $level
     * @return void
     */
    private function handleDeprecationError(string $message, string $file, int $line, int $level): void
    {
        $this->log($level, $message);
    }

    /**
     * @param Throwable $th
     * @return void
     * @throws Throwable
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
     * @return void
     * @throws Throwable
     */
    public function handleShutdown(): void
    {
        self::$reserveMemory = null;
        $error               = error_get_last();
        if ($error && $this->isFatal($error['type'])) {
            $this->handleException(
                new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line'])
            );
        }
    }

    /**
     * @param int $level
     * @param string $message
     * @return bool
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
     * @return ExceptionHandler
     */
    private function getHandler(): ExceptionHandler
    {
        return $this->app[ExceptionHandler::class];
    }

    private function isDeprecation(int $level): bool
    {
        return in_array($level, [E_DEPRECATED, E_USER_DEPRECATED]);
    }

    private function isFatal(int $level): bool
    {
        return in_array($level, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }
}
