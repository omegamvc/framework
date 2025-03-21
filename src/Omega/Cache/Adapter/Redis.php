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
use Redis as R;
use Omega\Cache\AbstractCacheItemPool;
use Omega\Cache\Item\HasExpirationDateInterface;
use Omega\Cache\Item\Item;
use Omega\Cache\Item\CacheItemInterface;

use function class_exists;
use function extension_loaded;

/**
 * Redis cache driver.
 * This adapter provides caching using Redis, a powerful in-memory data store.
 * It implements CacheItemPoolInterface for managing cache items with Redis as the backend.
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
class Redis extends AbstractCacheItemPool
{
    /**
     * The Redis driver instance.
     *
     * @var R Holds the Redis driver instance.
     */
    protected R $driver;

    /**
     * Constructor to initialize the Redis adapter with a Redis instance and options.
     *
     * @param R                    $redis   The Redis driver instance being used for this cache pool.
     * @param array<string, mixed> $options Options for configuring the cache pool.
     * @return void
     */
    public function __construct(
        protected R $redis,
        array $options = []
    ) {
        parent::__construct($options);

        $this->driver = $redis;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        return $this->driver->flushDB();
    }

    /**
     * {@inheritdoc}
     * @throws DateMalformedStringException
     */
    public function getItem(string $key): CacheItemInterface
    {
        $value = $this->driver->get($key);
        $item = new Item($key);

        if ($value !== false) {
            $item->set($value);
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem(string $key): bool
    {
        if ($this->hasItem($key)) {
            return (bool) $this->driver->del($key);
        }

        // If the item doesn't exist, no error
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item): bool
    {
        if ($item instanceof HasExpirationDateInterface) {
            $ttl = $this->convertItemExpiryToSeconds($item);

            if ($ttl > 0) {
                return $this->driver->setex($item->getKey(), $ttl, $item->get());
            }
        }

        return $this->driver->set($item->getKey(), $item->get());
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem(string $key): bool
    {
        return $this->driver->exists($key);
    }

    /**
     * Checks if the Redis extension and class are available for use.
     *
     * @return bool Returns true if Redis is available, false otherwise.
     */
    public static function isSupported(): bool
    {
        return extension_loaded('redis') && class_exists('Redis');
    }
}
