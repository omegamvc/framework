<?php

/**
 * Part of Omega - Support Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Facade;

/**
 * Interface FacadeInterface.
 *
 * The `FacadeInterface` defines the contract for all facade classes within the application.
 * It requires the implementation of the `getFacadeAccessor` method, which is crucial for
 * accessing the underlying instance from the application container. This allows facades
 * to provide a static interface to underlying services, making them easier to use
 * throughout the application.
 *
 * Implementing this interface ensures that all facades follow a consistent pattern for
 * resolving their underlying services, facilitating dependency injection and improving
 * maintainability.
 *
 * @category   Omega
 * @package    Facade
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
interface FacadeInterface
{
    /**
     * Get the facade accessor.
     *
     * This method must be implemented by subclasses to return the key used to resolve
     * the underlying instance from the application container.
     *
     * @return string Return the key used to access the underlying instance.
     */
    public static function getFacadeAccessor(): string;
}
