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

/**
 * In-memory cache driver.
 *
 * This adapter provides a simple in-memory caching mechanism with expiration.
 * It stores cache items in a memory array and handles expiration times.
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
class Memory extends AbstractCacheItemPool
{
    /**
     * In-memory cache storage.
     *
     * This property holds the cache items in an associative array.
     * Each cache item is an array containing the value of the cache entry and its expiration time.
     *
     * @var array<string, array{value: mixed, expires: float|int|null}> $cache
     */
    private array $cache = [];

    /**
     * {@iheritdoc}
     */
    public function clear(): bool
    {
        $this->cache = [];
        return true;
    }

    /**
     * {@iheritdoc}
     * @throws DateMalformedStringException
     */
    public function getItem(string $key): CacheItemInterface
    {
        $item = new Item($key);

        if ($this->hasItem($key)) {
            $item->set($this->cache[$key]['value']);
        }

        return $item;
    }

    /**
     * {@iheritdoc}
     * @throws DateMalformedStringException
     */
    public function getItems(array $keys = []): array
    {
        $items = [];

        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem(string $key): bool
    {
        unset($this->cache[$key]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item): bool
    {
        $expires = $item instanceof HasExpirationDateInterface
            ? $this->convertItemExpiryToSeconds($item)
            : 0;

        $expiresAt = $expires > 0 ? time() + $this->options['seconds'] : time() + $expires;

        $this->cache[$item->getKey()] = [
            'value'   => $item->get(),
            'expires' => $expiresAt,
        ];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem(string $key): bool
    {
        return isset($this->cache[$key])
            && ($this->cache[$key]['expires'] === null || $this->cache[$key]['expires'] > time());
    }

    /**
     * {@inheritdoc}
     */
    public static function isSupported(): bool
    {
        return true;
    }
}
