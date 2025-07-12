<?php

declare(strict_types=1);

namespace Omega\Text\Exceptions;

use InvalidArgumentException;

/**
 * @internal
 */
final class NoReturnException extends InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $method, string $original_text)
    {
        parent::__construct('Method ' . $method . ' with ' . $original_text . ' doest return anythink.');
    }
}
