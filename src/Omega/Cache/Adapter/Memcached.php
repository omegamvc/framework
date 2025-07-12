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
use Memcached as M;
use Omega\Cache\AbstractCacheItemPool;
use Omega\Cache\Exception\RuntimeException;
use Omega\Cache\Item\HasExpirationDateInterface;
use Omega\Cache\Item\Item;
use Omega\Cache\Item\CacheItemInterface;

use function array_key_exists;
use function class_exists;
use function extension_loaded;
use function is_array;
use function method_exists;
use function sprintf;

/**
 * Memcached cache adapter for the Omega framework.
 *
 * This class provides an implementation of a cache item pool using Memcached
 * as the backend storage mechanism.
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
class Memcached extends AbstractCacheItemPool
{
    /**
     * The Memcached driver instance.
     *
     * @var M Holds the Memcached driver used for caching operations.
     */
    protected M $driver;

    /**
     * Constructor.
     *
     * Initializes the Memcached adapter and sets up the caching options.
     *
     * @param M                    $memcached The Memcached driver instance.
     * @param array<string, mixed> $options   An associative array of configuration options.
     * @return void
     */
    public function __construct(M $memcached, array $options = [])
    {
        parent::__construct($options);

        $this->driver = $memcached;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        return $this->driver->flush();
    }

    /**
     * {@inheritdoc}
     * @throws DateMalformedStringException
     */
    public function getItem(string $key): CacheItemInterface
    {
        $value = $this->driver->get($key);
        $code  = $this->driver->getResultCode();
        $item  = new Item($key);

        if ($code === M::RES_SUCCESS) {
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
        $data = $this->driver->getMulti($keys);

        $result = [];

        foreach ($keys as $key) {
            $item = new Item($key);

            // On some platforms $data may be a boolean false
            if (is_array($data) && array_key_exists($key, $data)) {
                $item->set($data[$key]);
            }

            $result[$key] = $item;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem(string $key): bool
    {
        if ($this->hasItem($key)) {
            $this->driver->delete($key);

            $rc = $this->driver->getResultCode();

            // If the item was not successfully removed nor did not exist then raise an error
            if (($rc !== M::RES_SUCCESS)) {
                throw new RuntimeException(
                    sprintf(
                        'Unable to remove cache entry for %s. Error message `%s`.',
                        $key,
                        $this->driver->getResultMessage()
                    )
                );
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys): bool
    {
        // HHVM doesn't support deleteMulti
        if (!method_exists($this->driver, 'deleteMulti')) {
            return parent::deleteItems($keys);
        }

        $deleted = $this->driver->deleteMulti($keys);

        foreach ($deleted as $key => $value) {
            /*
             * The return of deleteMulti is not consistent with the documentation for error cases,
             * so check for an explicit boolean true for successful deletion
             */
            if ($value !== true && $value !== M::RES_NOTFOUND) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item): bool
    {
        $ttl = $item instanceof HasExpirationDateInterface
            ? $this->convertItemExpiryToSeconds($item)
            : 0;

        $ttl = $ttl > 0 ? (is_int($this->options['seconds']) ? $this->options['seconds'] : 0) : $ttl;

        return $this->driver->set($item->getKey(), $item->get(), $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem(string $key): bool
    {
        $this->driver->get($key);

        return $this->driver->getResultCode() !== M::RES_NOTFOUND;
    }

    /**
     * Checks if the Memcached extension is available.
     *
     * This method determines whether the Memcached PHP extension is loaded
     * and the Memcached class is available.
     *
     * @return bool Returns true if Memcached is supported, false otherwise.
     */
    public static function isSupported(): bool
    {
        return extension_loaded('Memcached') && class_exists('Memcached');
    }
}
