<?php

/**
 * Part of Omega - Cache Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Cache\Adapter;

use DateMalformedStringException;
use Omega\Cache\AbstractCacheItemPool;
use Omega\Cache\Item\HasExpirationDateInterface;
use Omega\Cache\Item\Item;
use Omega\Cache\Item\CacheItemInterface;

use function apcu_clear_cache;
use function apcu_delete;
use function apcu_exists;
use function apcu_fetch;
use function apcu_store;
use function extension_loaded;
use function is_array;
use function ini_get;
use function php_sapi_name;

/**
 * APCu Adapter class.
 *
 * The ApcuAdapter class is a cache driver that utilizes the APCu extension for object
 * caching in PHP. It implements the CacheItemPoolInterface from the PSR-6 caching standard,
 * providing a simple interface for storing, retrieving, and deleting cache items.
 *
 * This adapter works specifically with APCu, a user cache that stores key-value pairs in shared
 * memory, and is ideal for caching frequently accessed data during the execution of a PHP application.
 * It is typically used to improve performance by reducing the need for repeated computations or database
 * queries.
 *
 * **Key Features**
 *
 * * **Clear Cache**:          Clears all cached items in APCu using the 'apcu_clear_cache` function.
 * * **Get Item**:             Retrieves a cached item by key. If the item exists, its value is returned; otherwise,
 *                             an empty item is created.
 * * **Get Multiple Items*:    Retrieves multiple cache items at once, returning an array of cache items by their keys.
 * * **Delete Item**:          Deletes a specific cache item if it exists, returning a boolean indicating success or
 *                             failure.
 * * **Save Item**:            Saves a cache item, including support for setting an expiration time (TTL) if the item
 *                             implements the `HasExpirationDateInterface`.
 * * **Check if Item Exists**: Checks whether a cache item exists in APCu.
 * * **Support Check**:        A static method `isSupported()` to verify if APCu is enabled and available, including
 *                             checking CLI-specific settings.
 *
 * This class is especially useful for applications needing high-performance caching in environments where the APCu
 * extension is available, such as PHP 7.x and 8.x setups. It ensures fast retrieval and storage of data in memory,
 * significantly improving response times in applications.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Adapter
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class Apcu extends AbstractCacheItemPool
{
    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        return apcu_clear_cache();
    }

    /**
     * {@inheritdoc}
     * @throws DateMalformedStringException
     */
    public function getItem(string $key): CacheItemInterface
    {
        $success = false;
        $value   = apcu_fetch($key, $success);
        $item    = new Item($key);

        if ($success) {
            $item->set($value);
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     * @throws DateMalformedStringException
     */
    public function getItems(array $keys = []): array
    {
        $items   = [];
        $success = false;
        $values  = apcu_fetch($keys, $success);

        if ($success && is_array($values)) {
            foreach ($keys as $key) {
                $items[$key] = new Item($key);

                if (isset($values[$key])) {
                    $items[$key]->set($values[$key]);
                }
            }
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem(string $key): bool
    {
        if ($this->hasItem($key)) {
            return apcu_delete($key);
        }

        // If the item doesn't exist, no error
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item): bool
    {
        $isExpirable = $item instanceof HasExpirationDateInterface;

        if ($isExpirable) {
            $ttl = $this->convertItemExpiryToSeconds($item);
        } else {
            $ttl = 0;
        }

        return apcu_store($item->getKey(), $item->get(), $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key): bool
    {
        return apcu_exists($key);
    }

    /**
     * Test to see if the CacheItemPoolInterface is available
     *
     * @return  boolean  True on success, false otherwise
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function isSupported(): bool
    {
        $supported = extension_loaded('apcu') && ini_get('apc.enabled');

        // If on the CLI interface, the `apc.enable_cli` option must also be enabled
        if ($supported && php_sapi_name() === 'cli') {
            $supported = ini_get('apc.enable_cli');
        }

        return (bool) $supported;
    }
}
