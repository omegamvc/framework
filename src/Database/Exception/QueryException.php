<?php

/**
 * Part of Omega CMS - Database Package.
 *
 * @see       https://omegacms.github.io
 *
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Database\Exception;

/*
 * @use
 */
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
 * @category    Omega
 * @package     Database
 * @subpackage  Exception
 *
 * @see        https://omegacms.github.io
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class QueryException extends Exception
{
}
