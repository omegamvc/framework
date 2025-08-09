<?php

declare(strict_types=1);

namespace Omega\Macroable\Exceptions;

/**
 * @internal
 */
final class MacroNotFoundException extends \InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $method_name)
    {
        parent::__construct(sprintf('Macro `%s` is not macro able.', $method_name));
    }
}
