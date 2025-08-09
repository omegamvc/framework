<?php

declare(strict_types=1);

namespace Omega\File\Exceptions;

/**
 * @internal
 */
final class FolderNotExists extends \InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $folder_location)
    {
        parent::__construct(sprintf('Folder location not exists `%s`', $folder_location));
    }
}
