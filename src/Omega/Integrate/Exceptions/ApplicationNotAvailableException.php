<?php

declare(strict_types=1);

namespace Omega\Integrate\Exceptions;

use RuntimeException;

class ApplicationNotAvailableException extends RuntimeException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct()
    {
        parent::__construct('Application not start yet!');
    }
}
