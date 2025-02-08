<?php

/**
 * Part of Omega - Support Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Facade\Facades;

use Omega\Facade\AbstractFacade;

/**
 * Class Cache.
 *
 * The `Cache` class serves as a facade for accessing the view component
 * within the application. By extending the `AbstractFacade`, it provides
 * a static interface for interacting with the underlying view functionality
 * registered in the application container.
 *
 * This class implements the `getFacadeAccessor` method, which returns
 * the key used to resolve the underlying view instance. This allows
 * for a clean and straightforward way to access view-related features
 * without needing to instantiate the underlying components directly.
 *
 * @category   Omega
 * @package    Facade
 * @subpackage Facades
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 *
 * @method static static flush()                                              Remove all cache value by its key.
 * @method static static forget(string $key)                                  Remove a single cache value by its key.
 * @method static mixed  get(string $key, mixed $default = null)              Retrieve a cached value by its key.
 * @method static bool   has(string $key)                                     Check if a value exists in the cache.
 * @method static static put(string $key, mixed $value, ?int $seconds = null) Store a value in the cache.
 */
class Cache extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    public static function getFacadeAccessor(): string
    {
        return 'cache';
    }
}
