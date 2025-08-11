<?php

declare(strict_types=1);

namespace Omega\Text\Exceptions;

class PropertyNotExistException extends AbstractTextException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $propertyName)
    {
        parent::__construct('Property `%s` not exist.', $propertyName);
    }
}
