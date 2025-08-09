<?php

declare(strict_types=1);

namespace Omega\Text\Exceptions;

/**
 * @internal
 */
final class PropertyNotExistException extends \InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $property_name)
    {
        parent::__construct(sprintf('Property `%s` not exist.', $property_name));
    }
}
