<?php

declare(strict_types=1);

namespace Omega\Time\Exceptions;

class PropertyNotSettableException extends AbstractTimeException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $propertyName)
    {
        parent::__construct('Property `%s` is not settable.', $propertyName);
    }
}
