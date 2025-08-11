<?php

declare(strict_types=1);

namespace Omega\Text\Exceptions;

class NoReturnException extends AbstractTextException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $method, string $originalText)
    {
        parent::__construct('Method %s called with %s does not return anything.', $method, $originalText);
    }
}
