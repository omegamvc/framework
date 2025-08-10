<?php

declare(strict_types=1);

namespace Omega\View\Exceptions;

class DirectiveNotRegisterException extends AbstractViewException
{
    public function __construct(string $name)
    {
        parent::__construct('Directive %s is not registered.', $name);
    }
}
