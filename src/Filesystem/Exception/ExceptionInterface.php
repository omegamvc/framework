<?php

/**
 * Part of Omega CMS - Filesystem Package.
 *
 * @see       https://omegacms.github.io
 *
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovanni. (https://omegacms.github.io)
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

/**
 * Interface for the Omega related exceptions.
 *
 * This interface serves as a marker for all exceptions that are specific
 * to the Omega filesystem component. It allows for type-hinting and
 * catching of exceptions that are specific to this namespace, ensuring
 * consistency and clarity in exception handling throughout the Omega
 * filesystem implementation.
 *
 * All custom exception classes related to the Omega filesystem should
 * implement this interface to provide a clear structure for error
 * management and to facilitate the identification of filesystem-related
 * errors in client code.
 *
 * @category    Omega
 * @package     Filesystem
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
interface ExceptionInterface
{
}
