<?php

/**
 * Part of Omega - Filesystem Package.
 *
 * @see       https://omegamvc.github.io
 *
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovanni. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Filesystem\Exception;

/*
 * @declare
 */
use InvalidArgumentException;

/**
 * Exception to be thrown when an unsupported adapter is used.
 *
 * This exception is thrown when an attempt is made to use an adapter
 * that is not supported by the filesystem. It extends the `InvalidArgumentException`
 * and implements the `ExceptionInterface` to maintain a consistent approach
 * to error handling within the filesystem.
 *
 * @category    Omega
 * @package     Filesystem
 * @subpackage  Exception
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class UnsupportedAdapterException extends InvalidArgumentException implements ExceptionInterface
{
}
