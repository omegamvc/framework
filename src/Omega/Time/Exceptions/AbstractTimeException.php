<?php

declare(strict_types=1);

namespace Omega\Time\Exceptions;

use InvalidArgumentException;

use function vsprintf;

abstract class AbstractTimeException extends InvalidArgumentException implements TimeExceptionInterface
{
    public function __construct(string $message, ...$args)
    {
        parent::__construct(vsprintf($message, ...$args));
    }
}
