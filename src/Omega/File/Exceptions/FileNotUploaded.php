<?php

declare(strict_types=1);

namespace Omega\File\Exceptions;

/**
 * @internal
 */
final class FileNotUploaded extends \RuntimeException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct()
    {
        parent::__construct('File not uploaded `%s`');
    }
}
