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

/**
 * NotFoundExceptionInterface
 *
 * The NotFoundExceptionInterface extends the ContainerExceptionInterface and is used to represent
 * exceptions related to the inability to find a specific service or component within the Omega
 * container. Implementing this interface allows for better categorization of container exceptions
 * that specifically deal with missing or unresolved services, ensuring that such errors are properly
 * handled and distinguished from other types of container-related exceptions.
 *
 * This interface does not define any methods, but it marks exceptions that indicate a "not found"
 * condition in the container, such as when a service or dependency is not registered or available
 * for resolution.
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
interface NotFoundExceptionInterface extends ContainerExceptionInterface
{
}
