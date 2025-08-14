<?php

declare(strict_types=1);

namespace Omega\Text\Exceptions;

use InvalidArgumentException;

use function sprintf;

abstract class AbstractTextException extends InvalidArgumentException implements TextExceptionInterface
{
    public function __construct(string $message, ...$args)
    {
        parent::__construct(sprintf($message, ...$args));
    }
}
