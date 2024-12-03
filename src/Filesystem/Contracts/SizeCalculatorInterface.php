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
 * Interface that adds size calculation support to an adapter.
 *
 * This interface defines a method for retrieving the size of a file
 * in the filesystem, identified by a specific key. Adapters implementing
 * this interface should provide a mechanism to calculate or fetch the file size.
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
interface SizeCalculatorInterface
{
    /**
     * Retrieves the size of the specified file (key).
     *
     * This method returns the size of a file identified by its key. If the size
     * cannot be determined, it should return `false`.
     *
     * @param string $key The file key for which the size is being retrieved.
     *
     * @return int|false The size of the file in bytes, or `false` if it cannot be determined.
     */
    public function size(string $key): int|false;
}
