<?php

declare(strict_types=1);

namespace Omega\Collection\Exception;

use InvalidArgumentException;

class ImmutableCollectionException extends InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct()
    {
        parent::__construct('Collection imutable can not be modify');
    }
}
