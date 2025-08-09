<?php

/**
 * Part of Omega - Application Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Application\Exceptions;

use RuntimeException;

/**
 * MissingInstalledFileException class.
 *
 * The `MissingInstalledFileException` is thrown when the installed.php file is missing from the
 * Composer vendor directory. This file contains metadata about installed packages and is essential
 * for proper dependency resolution.
 *
 * Possible causes include:
 *
 * - Running the application before installing dependencies with Composer.
 * - Deleting the vendor directory or its contents without reinstalling dependencies.
 * - An incomplete or corrupted Composer installation.
 *
 * To resolve this issue, try running:
 * ```sh
 * composer dump-autoload -o
 * ```
 *
 * If the problem persists, consider reinstalling dependencies with:
 * ```sh
 * composer install
 * ```
 *
 * @category   System
 * @package    Application
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class MissingInstalledFileException extends RuntimeException
{
}
