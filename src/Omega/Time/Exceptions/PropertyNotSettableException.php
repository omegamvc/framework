<?php

declare(strict_types=1);

namespace Omega\Time\Exceptions;

use InvalidArgumentException;

use function sprintf;

class PropertyNotSettableException extends InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $propertyName)
    {
        parent::__construct(sprintf('Property `%s` not set able.', $propertyName));
    }
}
