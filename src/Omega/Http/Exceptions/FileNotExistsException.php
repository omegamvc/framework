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

use function sprintf;

/**
 * Class FileNotExistsException
 *
 * Thrown when a specified file path does not exist in the filesystem.
 *
 * Example usage:
 * ```php
 * if (!file_exists($path)) {
 *     throw new FileNotExistsException($path);
 * }
 * ```
 * @category   Omega
 * @package    Http
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class FileNotExistsException extends InvalidArgumentException implements HttpExceptionInterface
{
    /**
     * Creates a new exception instance.
     *
     * @param string $fileLocation The expected file location that does not exist.
     * @return void
     */
    public function __construct(string $fileLocation)
    {
        parent::__construct(sprintf('File location not exists `%s`', $fileLocation));
    }
}
