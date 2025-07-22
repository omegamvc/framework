<?php

/**
 * Part of Omega - Container Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Container\Exceptions;

use RuntimeException;

/**
 * Exception thrown when the creation of a directory fails.
 *
 * This exception is used to indicate an error during
 * the process of creating a directory in the filesystem,
 * typically due to permission issues, invalid paths, or other
 * filesystem-related problems.
 *
 * @category   Omega
 * @package    Container
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class DirectoryCreationException extends RuntimeException implements ContainerExceptionInterface
{
}
