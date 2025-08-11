<?php

declare(strict_types=1);

namespace Omega\View\Exceptions;

class DirectiveCanNotBeRegisterException extends AbstractViewException
{
    public function __construct(string $name, string $useBy)
    {
        parent::__construct('Directive "%s" cannot be used; it has already been used in "%s".', $name, $useBy);
    }
}
