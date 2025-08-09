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

namespace Omega\Container;

/**
 * Container aware interface class.
 *
 * Defines a contract for classes that require access to a Container instance.
 * This interface ensures that implementing classes can interact with a shared
 * `Container`, allowing them to retrieve and set the instance as needed.
 *
 * By using this interface, classes remain loosely coupled while still benefiting
 * from dependency resolution and service management provided by the Container.
 *
 * @category  Omega
 * @package   Container
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */
interface ContainerAwareInterface
{
    /**
     * Retrieves the current Container instance.
     *
     * If no instance has been set, a new one is automatically created.
     *
     * @return Container The current Container instance.
     */
    public function getContainer(): Container;

    /**
     * Sets a custom Container instance.
     *
     * This method allows manually defining the Container instance
     * that should be used.
     *
     * @param Container $container The Container instance to set.
     * @return void
     */
    public function setContainer(Container $container): void;
}
