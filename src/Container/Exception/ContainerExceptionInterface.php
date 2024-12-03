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

use Throwable;

/**
 * Base interface representing a generic exception in a container.
 *
 * The `ContainerExceptionInterface` is a generic interface that represents exceptions
 * thrown by a container when an error occurs.
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
interface ContainerExceptionInterface extends Throwable
{
}
