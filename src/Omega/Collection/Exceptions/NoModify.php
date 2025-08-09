<?php

declare(strict_types=1);

namespace Omega\Collection\Exceptions;

/**
 * @internal
 */
final class NoModify extends \InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct()
    {
        parent::__construct('Collection imutable can not be modify');
    }
}
