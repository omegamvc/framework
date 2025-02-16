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

use RuntimeException as PhpRuntimeException;

/**
 * Exception thrown when an error occurs during cache operations.
 *
 * This exception is used within the Omega caching system to signal runtime errors that prevent
 * proper cache functionality. It extends PHP's native RuntimeException and implements the
 * CacheException interface for compatibility with the caching system.
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
class RuntimeException extends PhpRuntimeException implements CacheExceptionInterface
{
}
