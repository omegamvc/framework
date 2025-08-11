<?php

declare(strict_types=1);

namespace Omega\View\Exceptions;

class ViewFileNotFoundException extends AbstractViewException
{
    public function __construct(string $fileName)
    {
        parent::__construct('View file not found: `%s`', $fileName);
    }
}
