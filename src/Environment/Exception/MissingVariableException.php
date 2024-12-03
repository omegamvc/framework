<?php

/**
 * Part of Omega CMS - Environment Package.
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
 * @author      Adriano Giovannini <omegacms@outlook.com>
 *
 * @see        https://omegacms.github.io
 *
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class MissingVariableException extends RuntimeException
{
}
