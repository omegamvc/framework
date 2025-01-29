<?php

/**
 * Part of Omega - Database Package.
 * php version 8.2
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Database\Exception;

use InvalidArgumentException;

/**
 * Connection exception class.
 *
 * The `ConnectionException` thrown for improperly configured database connections.
 * This exception is a subclass of InvalidArgumentException and is used to represent
 * errors that occur when a database connection is not properly configured.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Exception
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class ConnectionException extends InvalidArgumentException
{
}
