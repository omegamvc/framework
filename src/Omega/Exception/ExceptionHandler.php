<?php

/**
 * Part of Omega - Exception Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Exception;

use Throwable;
use Omega\Session\Storage\NativeStorage;
use Omega\Validation\Exception\ValidationException;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * ExceptionHandler class.
 *
 * The `ExceptionHandler` class provides utility methods for handling exceptions
 * in Omega applications.
 *
 * @category  Omega
 * @package   Exception
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */
class ExceptionHandler
{
    /**
     * Show Throwable.
     *
     * This method handles and displays exceptions or errors based on the environment.
     * In a development environment ('APP_ENV' === 'dev'), it displays detailed error
     * information using the error handler. In other environments, it may perform
     * different actions depending on the type of exception.
     *
     * @param Throwable $throwable Holds an instance of Throwable (Exception or Error).
     *
     * @return mixed Returns a response or performs actions based on the exception type.
     *
     * @throws Throwable
     */
    public function showThrowable(Throwable $throwable): mixed
    {
        if ($throwable instanceof ValidationException) {
            return $this->showValidationException($throwable);
        }

        if (env('APP_ENV') && env('APP_ENV') === 'dev') {
            $this->showFriendlyThrowable($throwable);
        }

        return null;
    }

    /**
     * Show Validation Exception.
     *
     * This method handles ValidationExceptions by storing error messages in the session
     * (if available) and redirecting back to the previous page. Useful for displaying
     * validation errors to the user.
     *
     * @param ValidationException $exception Holds an instance of ValidationException.
     *
     * @return mixed Returns a redirection or performs actions based on the exception.
     */
    public function showValidationException(ValidationException $exception): mixed
    {
        if ($session = session()) {
            /* @var NativeStorage $session */
            $session->put($exception->getSessionName(), $exception->getErrors());
        }

        return redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * Initialize ErrorHandler
     *
     * This method initializes the error handler for displaying user-friendly
     * error pages in a development environment ('APP_ENV' === 'dev').
     *
     * @param Throwable $throwable Holds an instance of Throwable (Exception or Error).
     * @return void
     * @throws Throwable
     */
    public function showFriendlyThrowable(Throwable $throwable): void
    {
        if (ob_get_level()) {
            ob_end_clean(); // Pulisce l'output buffer se presente
        }

        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());
        $whoops->allowQuit(false); // Evita che Whoops faccia exit() di sua iniziativa
        $whoops->writeToOutput(true); // Forza la scrittura a video
        $whoops->handleException($throwable); // Esegue Whoops

        exit; // Ferma l'applicazione, altrimenti continua dopo Whoops
    }
}
