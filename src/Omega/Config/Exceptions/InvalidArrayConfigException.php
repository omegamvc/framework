<?php

/**
 * Part of Omega - Config Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Config\Exceptions;

use RuntimeException;

/**
 * Exception thrown when an array-based configuration is invalid.
 *
 * This exception is triggered if the provided configuration array is empty
 * or does not follow the expected associative structure.
 *
 * @category   Omega
 * @package    Config
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class InvalidArrayConfigException extends RuntimeException implements ConfigExceptionInterface
{
}