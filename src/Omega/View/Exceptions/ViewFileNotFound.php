<?php

declare(strict_types=1);

namespace Omega\View\Exceptions;

class ViewFileNotFound extends \InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $file_name)
    {
        parent::__construct(sprintf('View path not exists `%s`', $file_name));
    }
}
