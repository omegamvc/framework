<?php

/**
 * Part of Omega - Cache Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Cache\Exception;

use Throwable;

/**
 * CacheExceptionInterface class.
 *
 * The CacheExceptionInterface is part of the Omega framework's Cache package. It serves as a marker
 * interface for all exceptions related to caching operations within the framework. By extending PHP's
 * built-in Throwable, it ensures that all cache-related exceptions can be caught and handled consistently.
 *
 * This interface is primarily intended to provide a common type for exceptions thrown by caching components,
 * making it easier to catch and process errors in a structured way.
 *
 * Since it does not declare any methods, its purpose is purely semantic, allowing developers to identify
 * and differentiate cache-related exceptions from other types of errors in the system.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Exception
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
interface CacheExceptionInterface extends Throwable
{
}
