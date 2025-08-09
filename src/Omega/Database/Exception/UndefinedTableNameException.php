<?php

/**
 * Part of Omega - Database Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Database\Exception;

use Exception;

/**
 * UndefinedTableNameException class.
 *
 * The `UndefinedTableNameException` is thrown when an operation in the database
 * package encounters a missing or undefined table name. This exception is
 * typically used to signal that a required table name has not been specified
 * during an interaction with the database, such as when executing a query or
 * performing table-specific operations.
 *
 * By providing this exception, the database package can more effectively handle
 * errors related to table name resolution, improving debugging and error
 * reporting in the application.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Exception
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class UndefinedTableNameException extends Exception
{
}
