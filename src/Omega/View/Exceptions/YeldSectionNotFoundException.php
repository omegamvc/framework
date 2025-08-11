<?php

declare(strict_types=1);

namespace Omega\View\Exceptions;

class YeldSectionNotFoundException extends AbstractViewException
{
    public function __construct(string $fileName)
    {
        parent::__construct('Yeld section not found: `%s`', $fileName);
    }
}
