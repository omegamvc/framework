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

/*
 * @declare
 */
use function sprintf;
use Exception;
use RuntimeException;

/**
 * Exception to be thrown when a file already exists.
 *
 * This exception is specifically designed to handle scenarios where
 * an attempt is made to create or overwrite a file that already
 * exists in the filesystem. It extends the RuntimeException,
 * providing a clear indication that this error is due to an
 * improper use of the filesystem operations.
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
class FileAlreadyExistsException extends RuntimeException implements ExceptionInterface
{
    /**
     * Constructs a new FileAlreadyExistsException.
     *
     * @param string         $key      The key (path) of the existing file.
     * @param int            $code     The error code (default is 0).
     * @param Exception|null $previous The previous exception for
     *                                 exception chaining (default is null).
     */
    public function __construct(
        protected string $key,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct(
            sprintf(
                'The file %s already exists and can not be overwritten.',
                $key
            ),
            $code,
            $previous
        );
    }
}
