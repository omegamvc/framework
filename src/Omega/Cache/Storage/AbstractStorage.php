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

namespace Omega\Cache\Storage;

use Closure;
use DateInterval;
use Omega\Cache\CacheInterface;

/**
 * Class AbstractStorage
 *
 * Base abstract class providing partial implementation of the {@see CacheInterface}.
 * It defines common logic shared across cache storage drivers while leaving core
 * methods — such as `get`, `set`, `clear`, and `has` — to be implemented by
 * concrete storage classes (e.g., FileStorage, ArrayStorage, RedisStorage).
 *
 * This class also provides default implementations for common cache operations
 * like handling multiple keys, increment/decrement behavior, and the `remember`
 * pattern for lazy caching.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Storage
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
abstract class AbstractStorage implements CacheInterface
{
    /**
     * {@inheritdoc}
     */
    abstract public function get(string $key, mixed $default = null): mixed;

    /**
     * {@inheritdoc}
     */
    abstract public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool;

    /**
     * {@inheritdoc}
     */
    abstract public function clear(): bool;

    /**
     * {@inheritdoc}
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool;

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $state = null;

        foreach ($keys as $key) {
            $result = $this->delete($key);

            $state = null === $state ? $result : $result && $state;
        }

        return $state ?: false;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function has(string $key): bool;

    /**
     * {@inheritdoc}
     */
    abstract public function increment(string $key, int $value): int;

    /**
     * {@inheritdoc}
     */
    public function decrement(string $key, int $value): int
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * {@inheritdoc}
     */
    public function remember(string $key, Closure $callback, int|DateInterval|null $ttl): mixed
    {
        $value = $this->get($key);

        if (null !== $value) {
            return $value;
        }

        $this->set($key, $value = $callback(), $ttl);

        return $value;
    }
}
