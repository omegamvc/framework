<?php

declare(strict_types=1);

namespace Omega\Collection\Exceptions;

use InvalidArgumentException;

class ImmutableCollectionException extends InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct()
    {
        parent::__construct('Collection immutable can not be modify');
    }
}
