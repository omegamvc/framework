<?php

/**
 * Part of Omega CMS - Filesystem Package.
 *
 * @see       https://omegacms.github.io
 *
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovanni. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Filesystem\Contracts;

/*
 * @use
 */
use Omega\Filesystem\File;
use Omega\Filesystem\Filesystem;

/**
 * Interface for a class responsible for file creation.
 *
 * This interface defines a method for creating instances of the `File` class.
 * It provides a contract for factories that instantiate files with a specific
 * key and filesystem context.
 *
 * @category    Omega
 * @package     Filesystem
 * @subpackage  Contracts
 *
 * @see        https://omegacms.github.io
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
interface FileFactoryInterface
{
    /**
     * Creates a new File instance.
     *
     * This method is responsible for generating a new `File` object
     * using the specified file key and the associated `Filesystem` instance.
     * It abstracts the logic of file creation, allowing different implementations
     * based on the specific requirements of the application.
     *
     * @param string     $key        The unique key that identifies the file in the filesystem.
     * @param Filesystem $filesystem The filesystem instance that will manage the file.
     *
     * @return File Returns a new instance of the `File` class.
     */
    public function createFile(string $key, Filesystem $filesystem): File;
}
