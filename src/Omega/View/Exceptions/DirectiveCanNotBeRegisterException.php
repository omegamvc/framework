<?php

declare(strict_types=1);

namespace Omega\View\Exceptions;

class DirectiveCanNotBeRegisterException extends AbstractViewException
{
    public function __construct(string $name, string $useBy)
    {
        parent::__construct('Directive %s cant be use, this has been use in %s.', $name, $useBy);
    }
}
