<?php

/**
 * Part of Omega CMS - Support Package.
 * php version 8.3
 *
 * @link      https://omegacms.github.io
 * @author    Adriano Giovannini <omegacms@outlook.com>
 * @copyright Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Cache\Factory;

use Omega\Support\Factory\GenericFactoryInterface;

/**
 * Cache factory interface.
 *
 * The `CacheFactoryInterface` is an extension of the GenericFactoryInterface, specifically
 * for creating cache-related instances. It follows the structure defined in the
 * `GenericFactoryInterface` and is used to standardize the creation of cache components in
 * the Omega CMS system.
 *
 * This interface inherits the `create` method from `GenericFactoryInterface`, allowing it to
 * return any type of cache-related object or value, based on an optional configuration array.
 *
 * - `create(?array $config = null): mixed`
 *   - The inherited method allows for the creation of cache instances, using an optional configuration array.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Factory
 * @link       https://omegacms.github.io
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
interface CacheFactoryInterface extends GenericFactoryInterface
{
}
