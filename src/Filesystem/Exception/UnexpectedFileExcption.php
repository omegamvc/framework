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
 * Exception to be thrown when an unexpected file exists.
 *
 * This exception is thrown when a file that is not anticipated to exist in the
 * filesystem is found. It extends the `RuntimeException` and implements the
 * `ExceptionInterface` to maintain a consistent exception handling approach
 * across the filesystem.
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
class UnexpectedFileExcption extends RuntimeException implements ExceptionInterface
{
    /**
     * Constructs a new `UnexpectedFileException` instance.
     *
     * @param string         $key      The key (path) of the file that was unexpectedly found.
     * @param int            $code     The exception code (default is 0).
     * @param Exception|null $previous Optional previous exception for chaining.
     */
    public function __construct(
        protected string $key,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct(
            sprintf(
                'The file "%s" was not supposed to exist.',
                $key
            ),
            $code,
            $previous
        );
    }
}
