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
 * QueryException class.
 *
 * The `QueryException` is thrown when there is an error executing a database query.
 * This exception serves to indicate issues such as malformed SQL statements,
 * syntax errors, or other problems encountered during the execution of a query
 * against the database.
 *
 * By utilizing this exception, the database package can provide clearer error
 * handling and reporting mechanisms, allowing developers to quickly identify
 * and rectify issues related to database interactions.
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
class QueryException extends Exception
{
}
