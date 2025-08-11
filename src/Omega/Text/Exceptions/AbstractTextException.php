<?php

declare(strict_types=1);

namespace Omega\Text\Exceptions;

use InvalidArgumentException;

use function vsprintf;

abstract class AbstractTextException extends InvalidArgumentException implements TextExceptionInterface
{
    public function __construct(string $message, ...$args)
    {
        parent::__construct(vsprintf($message, ...$args));
    }
}
