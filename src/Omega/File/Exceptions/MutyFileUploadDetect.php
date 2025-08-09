<?php

declare(strict_types=1);

namespace Omega\File\Exceptions;

/**
 * @internal
 */
final class MutyFileUploadDetect extends \InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct()
    {
        parent::__construct('Single files detected use `UploadMultyFile` instances of `UploadFile`');
    }
}
