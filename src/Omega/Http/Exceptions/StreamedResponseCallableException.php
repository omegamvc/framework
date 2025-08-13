<?php

declare(strict_types=1);

namespace Omega\Http\Exceptions;

use Exception;

class StreamedResponseCallableException extends Exception
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct()
    {
        parent::__construct('Stream callback must not be null');
    }
}
