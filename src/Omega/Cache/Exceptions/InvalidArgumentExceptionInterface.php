<?php

/**
 * Part of Omega - Cache Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Cache\Exceptions;

/**
 * The InvalidArgumentExceptionInterface is part of the Omega framework's Cache package and extends
 * the CacheExceptionInterface.
 *
 * This interface is a marker for exceptions related to invalid arguments passed to caching components.
 * By inheriting from CacheExceptionInterface, it ensures that such errors are categorized as cache-related
 * exceptions, allowing for structured error handling within the framework.
 *
 * Since it does not define any methods, its role is purely semantic, providing a clear distinction between
 * different types of cache exceptions.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
interface InvalidArgumentExceptionInterface extends CacheExceptionInterface
{
}
