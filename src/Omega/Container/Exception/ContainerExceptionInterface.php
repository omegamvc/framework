<?php

/**
 * Part of Omega - Container Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Container\Exception;

use Throwable;

/**
 * ContainerExceptionInterface
 *
 * The `ContainerExceptionInterface` serves as a marker interface within the Omega framework's
 * container package. It is used to group all exceptions related to the container subsystem.
 * Implementing this interface allows for consistent handling and categorization of container-specific
 * exceptions, providing a clear separation between general exceptions and those specifically tied
 * to the container's functionality.
 *
 * This interface does not define any methods, but is intended to be extended by other exception
 * classes that are specific to errors encountered when interacting with the Omega container.
 *
 * @category   Omega
 * @package    Container
 * @subpackage Exception
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
interface ContainerExceptionInterface extends Throwable
{
}
