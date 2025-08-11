<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use RuntimeException;

use function sprintf;

class FacadeObjectNotSetException extends RuntimeException
{
    public function __construct(string $className)
    {
        parent::__construct(
            sprintf(
                "The facade instance for %s has not been set. " .
                "Please ensure that the facade is registered with the application container " .
                "and that the container is configured correctly.",
                $className
            )
        );
    }
}
