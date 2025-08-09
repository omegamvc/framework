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

namespace Omega\Support\Singleton\Exceptions;

use RuntimeException;

/**
 * Exception thrown when the singleton class cannot be determined.
 *
 * This exception is used in situations where the singleton class name cannot
 * be resolved, typically during the instantiation of the singleton. This may
 * happen if the class is not properly defined or does not exist in the current
 * context. It extends `RuntimeException` to signal a runtime error.
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
class UndefinedSingletonClassException extends RuntimeException
{
}
