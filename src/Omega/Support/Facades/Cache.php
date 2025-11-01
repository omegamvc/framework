<?php

/**
 * Part of Omega - Facades Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Facades;

use Closure;
use DateInterval;
use Omega\Cache\CacheInterface;
use Omega\Cache\CacheFactory;

/**
 * Facade for the Cache service.
 *
 * This facade provides a static interface to the underlying `Cache` instance
 * resolved from the application container. It allows convenient static-style
 * calls while still relying on dependency injection and the container under the hood.
 *
 * Usage of this facade does not create a global state; the underlying instance
 * is still managed by the container and may be swapped, mocked, or replaced
 * for testing or customization purposes.
 *
 * @category   Omega
 * @package    Support
 * @subpackges Facades
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
 * @method static CacheFactory   setDefaultDriver(CacheInterface $driver)
 * @method static CacheFactory   setDriver(string $driver_name, $driver)
 * @method static CacheInterface driver(?string $driver_name = null)
 * @method static mixed          get(string $key, mixed $default = null)
 * @method static bool           set(string $key, mixed $value, DateInterval|int|null $ttl = null)
 * @method static bool           delete(string $key)
 * @method static bool           clear()
 * @method static iterable       getMultiple(iterable $keys, mixed $default = null)
 * @method static bool           setMultiple(iterable $values, DateInterval|int|null $ttl = null)
 * @method static bool           deleteMultiple(iterable $keys)
 * @method static bool           has(string $key)
 * @method static int            increment(string $key, int $value)
 * @method static int            decrement(string $key, int $value)
 * @method static mixed          remember(string $key, Closure $callback, DateInterval|int|null $ttl)
 *
 * @see CacheFactory
 */
final class Cache extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    public static function getFacadeAccessor(): string
    {
        return 'cache';
    }
}
