<?php

/**
 * Part of Omega - Http Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Http\Exceptions;

use RuntimeException;

/**
 * Class FileNotUploadedException
 *
 * Thrown when an expected file upload operation has failed or no file was uploaded.
 *
 * @category   Omega
 * @package    Http
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class FileNotUploadedException extends RuntimeException implements HttpExceptionInterface
{
    /**
     * Creates a new exception instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('File not uploaded `%s`');
    }
}
