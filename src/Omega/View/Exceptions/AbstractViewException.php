<?php

declare(strict_types=1);

namespace Omega\View\Exceptions;

use InvalidArgumentException;

use function sprintf;

abstract class AbstractViewException extends InvalidArgumentException implements ViewExceptionInterface
{
    public function __construct(string $message, ...$args)
    {
        parent::__construct(sprintf($message, $args));
    }
}
