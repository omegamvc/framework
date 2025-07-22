<?php

/**
 * Part of Omega - Exception Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Exceptions;

use RuntimeException;

/**
 * Exception thrown when the global Application instance is not available.
 *
 * This typically occurs when attempting to use the `app()` helper
 * before the application has been properly initialized.
 *
 * @category  Omega
 * @package   Exceptions
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class ApplicationNotAvailableException extends RuntimeException
{
    /**
     * Create a new ApplicationNotAvailable exception instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('The application instance is not available.');
    }
}
