<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Exceptions;

use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Exceptions\ContainerExceptionInterface;

/**
 * Invalid DI definitions.
 */
class InvalidDefinitionException extends \Exception implements ContainerExceptionInterface
{
    public static function create(DefinitionInterface $definition, string $message, ?\Exception $previous = null) : self
    {
        return new self(sprintf(
            '%s' . \PHP_EOL . 'Full definition:' . \PHP_EOL . '%s',
            $message,
            (string) $definition
        ), 0, $previous);
    }
}
