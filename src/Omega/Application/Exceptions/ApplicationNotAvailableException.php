<?php

/**
 * Part of Omega - Application Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Application\Exceptions;

use RuntimeException;

/**
 * Exception thrown when the application is not available.
 *
 * This exception is thrown when an attempt is made to access the application
 * container before it has been properly initialized or started. It signals that
 * the application is not yet ready for use. This exception extends the
 * `RuntimeException` class to indicate that the error occurs during runtime.
 *
 * @category   System
 * @package    Application
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class ApplicationNotAvailableException extends RuntimeException
{
    /**
     * Creates a new Exception instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct(
            'Application is not available yet.
            Please ensure the application is properly initialized before accessing it.'
        );
    }
}
