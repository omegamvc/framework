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
 * Container aware trait class.
 *
 * Provides a default implementation for managing a shared Container instance.
 *
 * This trait enables a class to be "Container-Aware" by supplying the logic
 * for retrieving and setting a `Container` instance. If no instance is explicitly
 * set, a new one is created on demand.
 *
 * This approach ensures consistency in dependency management while maintaining flexibility
 * across different parts of the application.
 *
 * @category  Omega
 * @package   Container
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */
trait ContainerAwareTrait
{
    /**
     * The shared Container instance.
     *
     * @var Container|null Holds the current Container instance or null if not set.
     */
    private static ?Container $object = null;

    /**
     * Retrieves the current Container instance.
     *
     * If no instance has been set, a new one is automatically created.
     *
     * @return Container The current Container instance.
     */
    public function getContainer(): Container
    {
        return static::$object ??= new Container();
    }

    /**
     * Sets a custom Container instance.
     *
     * This method allows manually defining the Container instance
     * that should be used.
     *
     * @param Container $container The Container instance to set.
     * @return void
     */
    public function setContainer(Container $container): void
    {
        static::$object = $container;
    }
}
