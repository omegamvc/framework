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

namespace Omega\Support\Facade\Facades;

use Omega\Cache\Item\CacheItemInterface;
use Omega\Support\Facade\AbstractFacade;

/**
 * Class Cache.
 * The `Cache` class serves as a facade for accessing the view component
 * within the application. By extending the `AbstractFacade`, it provides
 * a static interface for interacting with the underlying view functionality
 * registered in the application container.
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
 * @method static CacheItemInterface getItem(string $key) Returns a Cache Item representing the specified key.
 * @method static iterable getItems(array $keys = []) Returns a traversable set of cache items.
 * @method static bool hasItem(string $key) Check if a value exists in the cache.
 * @method static bool clear() Delete all item in the pool.
 * @method static bool deleteItem(string $key) Removes the item from the pool.
 * @method static bool deleteItems(array $keys) Removes multiple items from the pool.
 * @method static bool save(CacheItemInterface $item) Persists a cache item immediately.
 * @method static bool saveDeferred(CacheItemInterface $item) Sets a cache item to be persisted later.
 * @method static bool commit() Persists any deferred cache items.
 * @method static static set(mixed $value) Sets the value represented by this cache item.
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
