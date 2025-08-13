<?php

declare(strict_types=1);

namespace Omega\Http\Exceptions;

use RuntimeException;

class FileNotUploadedException extends RuntimeException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct()
    {
        parent::__construct('File not uploaded `%s`');
    }
}
