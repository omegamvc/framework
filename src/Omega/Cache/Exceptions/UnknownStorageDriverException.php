<?php

declare(strict_types=1);

namespace Omega\Cache\Exceptions;

use Exception;

use function sprintf;

class UnknownStorageDriverException extends Exception
{
    public function __construct(string $driverName)
    {
        parent::__construct(sprintf('Unresolved storage %s', $driverName));
    }
}
