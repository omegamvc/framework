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

namespace Omega\Container\Contracts\Factory;

/**
 * The `GenericFactoryInterface` is a contract for creating instances of objects based \
 * on a provided configuration array. This interface declares a method `create` that accepts
 * an optional configuration array and returns a mixed type result, which can be any type of
 * object or value.
 *
 * @category   Omega
 * @package    Container
 * @subpackage Factory
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
interface GenericFactoryInterface
{
    /**
     * Creates and returns an instance of an object based on the provided configuration.
     *
     * @param array<string, mixed>|null $config Holds an optional configuration array that may be used to influence the
     *                                          creation of the object. If no configuration is provided, default
     *                                          settings may be applied.
     * @return mixed Return the created object or value. The return type is flexible, allowing or any type to be
     *               returned, depending on the implementation.
     */
    public function create(?array $config = null): mixed;
}
