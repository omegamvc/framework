<?php

/**
 * Part of Omega - Logging Package.
 *
 * @see       https://omegamvc.github.io
 *
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Logging\Exception;

/*
 * @use
 */
use InvalidArgumentException;

/**
 * Class LogArgumentException.
 *
 * This exception is thrown when an invalid argument is passed to a logging-related method or function.
 * It extends the built-in InvalidArgumentException and is specific to logging operations within the Omega framework.
 *
 * @category    Omega
 * @package     Logging
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
class LogArgumentException extends InvalidArgumentException
{
}