<?php

/**
 * Part of Omega - Cache Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Cache;

use Omega\Cache\Exceptions\InvalidArgumentException;
use Omega\Cache\Item\CacheItemInterface;

/**
 * Defines a contract for a cache pool that stores and manages cache items.
 *
 * This interface extends the PSR-6 CacheItemPoolInterface, providing an additional method
 * to check whether the cache pool implementation is available in the current environment.
 *
 * @category   Omega
 * @package    Cache
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
interface CacheItemPoolInterface
{
    /**
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return a CacheItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key The key for which to return the corresponding Cache Item.
     * @return CacheItemInterface The corresponding Cache Item.
     * @throws InvalidArgumentException if the $key string is not a legal value a InvalidArgumentException throw.
     */
    public function getItem(string $key): CacheItemInterface;

    /**
     * Returns a traversable set of cache items.
     *
     * @param string[] $keys An indexed array of keys of items to retrieve.
     * @return iterable<string, CacheItemInterface> An iterable collection of Cache Items keyed by the cache keys of
     *                                              each item. A Cache item will be returned for each key, even if that
     *                                              key is not found. However, if no keys are specified then an empty
     *                                              traversable MUST be returned instead.
     * @throws InvalidArgumentException if the $key string is not a legal value a InvalidArgumentException throw.
     */
    public function getItems(array $keys = []): iterable;

    /**
     * Confirms if the cache contains specified cache item.
     *
     * Note: This method MAY avoid retrieving the cached value for performance reasons.
     * This could result in a race condition with CacheItemInterface::get(). To avoid
     * such situation use CacheItemInterface::isHit() instead.
     *
     * @param string $key The key for which to check existence.
     * @return bool True if item exists in the cache, false otherwise.
     * @throws InvalidArgumentException If the $key string is not a legal value a InvalidArgumentException thrown.
     */
    public function hasItem(string $key): bool;

    /**
     * Deletes all items in the pool.
     *
     * @return bool True if the pool was successfully cleared. False if there was an error.
     */
    public function clear(): bool;

    /**
     * Removes the item from the pool.
     *
     * @param string $key The key to delete.
     * @return bool True if the item was successfully removed. False if there was an error.
     * @throws InvalidArgumentException If the $key string is not a legal value a InvalidArgumentException thrown.
     */
    public function deleteItem(string $key): bool;

    /**
     * Removes multiple items from the pool.
     *
     * @param string[] $keys An array of keys that should be removed from the pool.
     * @return bool True if the items were successfully removed. False if there was an error.
     * @throws InvalidArgumentException If any of the keys in $keys are not a legal value.
     */
    public function deleteItems(array $keys): bool;

    /**
     * Persists a cache item immediately.
     *
     * @param CacheItemInterface $item The cache item to save.
     * @return bool True if the item was successfully persisted. False if there was an error.
     */
    public function save(CacheItemInterface $item): bool;

    /**
     * Sets a cache item to be persisted later.
     *
     * @param CacheItemInterface $item The cache item to save.
     * @return bool False if the item could not be queued or if a commit was attempted and failed. True otherwise.
     */
    public function saveDeferred(CacheItemInterface $item): bool;

    /**
     * Persists any deferred cache items.
     *
     * @return bool True if all not-yet-saved items were successfully saved or there were none. False otherwise.
     */
    public function commit(): bool;

    /**
     * Checks if the cache pool implementation is available.
     *
     * This method determines whether the current environment supports the cache pool.
     * It can be used to verify dependencies or configuration settings before attempting
     * to interact with the cache.
     *
     * @return bool Returns true if the cache pool is available, false otherwise.
     */
    public static function isSupported(): bool;
}
