<?php

/**
 * Part of Omega - Http Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Http\Exceptions;

use InvalidArgumentException;

/**
 * Class MultiFileUploadDetectException
 *
 * Thrown when multiple files are detected but a single file upload class is used.
 * This helps enforce the use of `UploadMultiFile` instead of `UploadFile` in multi-upload scenarios.
 *
 * @category   Omega
 * @package    Http
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class MutiFileUploadDetectException extends InvalidArgumentException implements HttpExceptionInterface
{
    /**
     * Creates a new exception instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Single files detected use `UploadMultiFile` instances of `UploadFile`');
    }
}
