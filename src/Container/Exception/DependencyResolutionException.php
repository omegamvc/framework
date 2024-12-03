<?php

/**
 * Part of Omega CMS - Container Package.
 * php version 8.3
 *
 * @link      https://omegacms.github.io
 * @author    Adriano Giovannini <omegacms@outlook.com>
 * @copyright Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Container\Exception;

use InvalidArgumentException;

/**
 * Exception class for handling errors in resolving a dependency.
 *
 * The `DependencyResolutionException` class extends `InvalidArgumentException` and
 * implements the `ContainerExceptionInterface`. It represents an exception that occurs
 * when there is an error in resolving a dependency within the container.
 *
 * @category   Omega
 * @package    Container
 * @subpackage Exception
 * @link       https://omegacms.github.io
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class DependencyResolutionException extends InvalidArgumentException implements ContainerExceptionInterface
{
}
