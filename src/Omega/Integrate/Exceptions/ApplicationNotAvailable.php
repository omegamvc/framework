<?php

declare(strict_types=1);

namespace Omega\Integrate\Exceptions;

/**
 * @internal
 */
final class ApplicationNotAvailable extends \RuntimeException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct()
    {
        parent::__construct('Apllication not start yet!');
    }
}
