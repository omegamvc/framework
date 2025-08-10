<?php

declare(strict_types=1);

namespace Omega\View\Exceptions;

class ViewFileNotFoundException extends AbstractViewException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $fileName)
    {
        parent::__construct('View path not exists `%s`', $fileName);
    }
}
