<?php

declare(strict_types=1);

namespace Omega\Http\Exceptions;

use InvalidArgumentException;

use function sprintf;

class FolderNotExistsException extends InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $folderLocation)
    {
        parent::__construct(
            sprintf(
                'Folder location not exists `%s`',
                $folderLocation
            )
        );
    }
}
