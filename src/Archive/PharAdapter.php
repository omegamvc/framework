<?php

/**
 * Part of Omega MVC - Archive Package
 * php version 8.2
 *
 * @link        https://omegamvc.github.io
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */

declare(strict_types=1);

namespace Omega\Archive;

use Phar;
use RuntimeException;
use Omega\Archive\Exception\PharRenameException;

use function array_keys;
use function dirname;
use function file_exists;
use function strlen;

/**
 * PharAdapter class.
 *
 * A class that implements the `AdapterInterface' for handling Phar (PHP Archive) files.
 * It allows operations like opening, reading, writing, deleting, and renaming files in a Phar archive.
 * The class utilizes the Phar extension to manipulate the contents of Phar archives.
 * Some methods such as deleting files and renaming rely on the Phar extension's capabilities.
 * It does not require manual closing, as the Phar class manages it automatically.
 *
 * @category    Omega
 * @package     Archive
 * @link        https://omegamvc.github.io
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
class PharAdapter extends AbstractAdapter
{
    /**
     * PharAdapter constructor.
     *
     * Initializes the PharAdapter with the given path to the Phar archive file.
     * A Phar object is created and associated with the archive file for managing the contents.
     *
     * @param string $pharFile The path to the Phar file to be used for the archive operations.
     * @return void
     * @throws RuntimeException if the Phar file not exists.
     */
    public function __construct(
        protected string $pharFile
    ) {
        if (!file_exists($pharFile)) {
            throw new RuntimeException("The Phar file '$pharFile' does not exist.");
        }

        $this->phar = new Phar($pharFile);
    }

    /**
     * {@inheritdoc}
     * @throws RuntimeException if failed to open Phar archive.
     */
    public function open(string $file): void
    {
        try {
            $this->phar->startBuffering();
            $this->phar->buildFromDirectory(dirname($file));
            $this->phar->stopBuffering();
        } catch (RuntimeException $e) {
            throw new RuntimeException("Failed to open Phar archive '$file'. " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
    }

    /**
     * {@inheritdoc}
     * @throws RuntimeException if the key not exists in Phar archive or failed to read from the key.
     */
    public function read(string $key): string|bool
    {
        if (!$this->phar->offsetExists($key)) {
            throw new RuntimeException("The key '$key' does not exist in the Phar archive.");
        }

        try {
            return $this->phar->getContents($key);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Failed to read from the key '$key'. " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     * @throws RuntimeExeption if failed to write the key.
     */
    public function write(string $key, string $content): int|bool
    {
        try {
            $this->phar[$key] = $content;
            return strlen($content);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Failed to write to the key '$key'. " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     * @throws RuntimeException if the key not exists or failed to delete the key.
     */
    public function delete(string $key): bool
    {
        if (!$this->phar->offsetExists($key)) {
            throw new RuntimeException("The key '$key' does not exist and cannot be deleted.");
        }

        try {
            unset($this->phar[$key]);
            return true;
        } catch (RuntimeException $e) {
            throw new RuntimeException("Failed to delete the key '$key'. " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $key): bool
    {
        return isset($this->phar[$key]);
    }

    /**
     * {@inheritdoc}
     * @throws RuntimeException if failed to retrieve key from the Phar archive.
     */
    public function keys(): array
    {
        try {
            return array_keys($this->phar->getFiles());
        } catch (RuntimeException $e) {
            throw new RuntimeException("Failed to retrieve keys from the Phar archive. " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     * @throws RuntimeException if the key not exists.
     */
    public function isDirectory(string $key): bool
    {
        if (!$this->phar->offsetExists($key)) {
            throw new RuntimeException("The key '$key' does not exist.");
        }

        return $this->phar[$key]->isDir();
    }

    /**
     * {@inheritdoc}
     * @throws RuntimeException if the key not exists or failed to retrieves the modification time for the key.
     */
    public function mtime(string $key): int|bool
    {
        if (!$this->phar->offsetExists($key)) {
            throw new RuntimeException("The key '$key' does not exist.");
        }

        try {
            return $this->phar[$key]->getMTime();
        } catch (RuntimeException $e) {
            throw new RuntimeException("Failed to retrieve modification time for '$key'. " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     * @throws RuntimeException
     * @throws PharRenameException if the renaming operation fails due to file not existing, target file existing, or
     *                             any other failure.
     */
    public function rename(string $sourceKey, string $targetKey): bool
    {
        if (!$this->phar->offsetExists($sourceKey)) {
            throw new PharRenameException("Source file '$sourceKey' does not exist.");
        }

        if ($this->phar->offsetExists($targetKey)) {
            throw new PharRenameException("Target file '$targetKey' already exists.");
        }

        try {
            $this->phar[$targetKey] = $this->phar[$sourceKey];
            unset($this->phar[$sourceKey]);
        } catch (RuntimeException $e) {
            throw new PharRenameException("Failed to rename '$sourceKey' to '$targetKey'. " . $e->getMessage(), 0, $e);
        }

        return true;
    }
}
