<?php

/**
 * Part of Omega - Config Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Config\Exceptions;

use RuntimeException;

/**
 * Exception thrown when a configuration file cannot be read.
 *
 * This exception is triggered when the system is unable to retrieve the content from
 * a configuration file due to missing permissions, an incorrect file path, or other
 * reading issues.
 *
 * @category   Omega
 * @package    Config
 * @subpackage Excptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class FileReadException extends RuntimeException implements ConfigExceptionInterface
{
}
