<?php

/**
 * Part of Omega -  Queue Package.
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

namespace Omega\Queue\Exception;

/*
 * @use
 */
use RuntimeException;

/**
 * Driver exception class.
 *
 * This `DriverException` class is thrown when an error occurs in the Queue package related
 * to drivers. It extends the RuntimeException class, indicating a runtime error in the queue
 * driver.
 *
 * @category    Omega
 * @package     Queue
 * @subpackage  Exceptions
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class QueueException extends RuntimeException
{
}
