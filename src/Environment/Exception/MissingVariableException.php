<?php

/**
 * Part of Omega - Environment Package.
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

namespace Omega\Environment\Exception;

/*
 * @use
 */
use RuntimeException;

/**
 * Exception thrown when a required environment variable is missing.
 *
 * This exception is triggered by the dotenv package when an expected
 * environment variable is not defined or cannot be found.
 *
 * @category    Omega
 * @package     Environment
 * @subpackage  Exception
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 *
 * @see        https://omegamvc.github.io
 *
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class MissingVariableException extends RuntimeException
{
}
