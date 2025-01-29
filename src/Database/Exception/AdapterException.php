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

use PDOException;

/**
 * Adapter exception class.
 *
 * The `AdapterException` thrown for database adapter-related errors.
 * This exception is a subclass of PDOException and is used to represent
 * errors that occur specifically in the context of database adapters.
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
class AdapterException extends PDOException
{
}
