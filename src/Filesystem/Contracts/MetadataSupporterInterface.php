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

/**
 * Interface that adds support for file metadata management.
 *
 * This interface defines methods for setting and retrieving metadata associated
 * with a specific file (or key) in the filesystem. Any adapter implementing this
 * interface should provide mechanisms to store and fetch metadata for files.
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
interface MetadataSupporterInterface
{
    /**
     * Sets metadata for the specified file (or key).
     *
     * This method allows setting an array of metadata information for a specific
     * file in the filesystem. The metadata could include custom information like
     * timestamps, file ownership, or any other descriptive data.
     *
     * @param string $key     The file key for which the metadata is being set.
     * @param array  $content An associative array containing the metadata to set.
     */
    public function setMetadata(string $key, array $content): void;

    /**
     * Retrieves metadata for the specified file (or key).
     *
     * This method fetches the metadata associated with the given file key from
     * the filesystem. It returns an associative array of metadata, which may
     * include information such as size, last modification time, and more.
     *
     * @param string $key The file key for which metadata is being retrieved.
     *
     * @return array An associative array containing the metadata of the file.
     */
    public function getMetadata(string $key): array;
}
