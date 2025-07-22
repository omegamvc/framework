<?php

/**
 * Part of Omega - Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Console\Exceptions;

use Throwable;

/**
 * Marker interface for all exceptions thrown within the Console package.
 *
 * This interface allows consumers to catch all console-related exceptions
 * using a single type hint, regardless of their specific class.
 *
 * All custom exceptions in the Console package should implement this interface,
 * either directly or via inheritance from ConsoleException.
 *
 * @category   Omega
 * @package    Console
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
interface ConsoleExceptionInterface extends Throwable
{
}
