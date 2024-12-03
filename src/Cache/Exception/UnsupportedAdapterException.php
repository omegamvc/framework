<?php

/**
 * Part of Omega CMS - Cache Package.
 *
 * @link      https://omegacms.github.io
 * @author    Adriano Giovannini <omegacms@outlook.com>
 * @copyright Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Cache\Exception;

use RuntimeException;

/**
 * Unsupported driver exception class.
 *
 * The `UnsupportedAdapterException` class is thrown when an unsupported cache
 * adapter type is encountered. It typically indicates that the requested cache
 * driver is not recognized or supported by the framework.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Exception
 * @link       https://omegacms.github.io
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class UnsupportedAdapterException extends RuntimeException
{
}
