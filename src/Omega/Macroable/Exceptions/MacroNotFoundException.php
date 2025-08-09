<?php

declare(strict_types=1);

namespace Omega\Macroable\Exceptions;

use InvalidArgumentException;

use function sprintf;

class MacroNotFoundException extends InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $methodName)
    {
        parent::__construct(sprintf('Macro `%s` is not macro able.', $methodName));
    }
}
