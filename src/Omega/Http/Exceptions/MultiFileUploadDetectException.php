<?php

declare(strict_types=1);

namespace Omega\Http\Exceptions;

use InvalidArgumentException;

class MultiFileUploadDetectException extends InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct()
    {
        parent::__construct('Single files detected use `UploadMultiFile` instances of `UploadFile`');
    }
}
