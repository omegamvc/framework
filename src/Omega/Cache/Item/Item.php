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

namespace Omega\Cache\Item;

use DateInterval;
use DateMalformedStringException;
use DateTime;
use DateTimeInterface;

use function is_int;

/**
 * Represents a cache item.
 *
 * This class provides an implementation of a cache item, including its key, expiration time, value,
 * and hit status. It supports multiple expiration formats, including integer-based TTL (time-to-live),
 * DateInterval, and DateTimeInterface. The item tracks whether it was successfully retrieved from the cache.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Item
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class Item extends AbstractItem
{
    /** @var DateTimeInterface Holds the timestamp at which the cache item expires. */
    private DateTimeInterface $expiration;

    /** @var mixed Holds the cached value. */
    private mixed $value = null;

    /** @var bool True if the item exists in the cache and is not expired, false otherwise. */
    private bool $hit = false;

    /**
     * Initializes a new cache item.
     *
     * @param string $key The unique key identifying the cache item.
     * @param DateInterval|DateTimeInterface|int|null $ttl The expiration time, specified as:
     *     - An integer representing seconds until expiration.
     *     - A DateInterval object.
     *     - A DateTimeInterface object for a fixed expiration time.
     *     - Null to use the default TTL of 900 seconds.
     * @return void
     * @throws DateMalformedStringException if the provided date string is malformed or not in a valid format.
     */
    public function __construct(private readonly string $key, DateInterval|DateTimeInterface|int|null $ttl = null)
    {
        $this->expiration = new DateTime('now + 900 seconds');

        $this->expiresAfter($ttl);
    }

    /**
     * Determines whether the cache item exists.
     *
     * Note: This method MAY avoid retrieving the actual cached value for performance reasons,
     * which could lead to a race condition between `exists()` and `get()`. To avoid this issue,
     * use `isHit()` instead.
     *
     * @return bool True if the cache item exists and is valid, false otherwise.
     */
    public function exists(): bool
    {
        return $this->isHit();
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function get(): mixed
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function set(mixed $value): static
    {
        $this->value = $value;
        $this->hit = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isHit(): bool
    {
        return $this->hit;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt(?DateTimeInterface $expiration): static
    {
        $this->expiration = $expiration;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DateMalformedStringException if the provided date string is malformed or not in a valid format.
     */
    public function expiresAfter(int|DateInterval|DateTimeInterface|null $time): static
    {
        if ($time instanceof DateInterval) {
            $this->expiration = (new DateTime())->add($time);
        } elseif ($time instanceof DateTimeInterface) {
            $this->expiration = $time;
        } elseif (is_int($time)) {
            $this->expiration = (new DateTime())->modify("+$time seconds");
        } else {
            // default
            $this->expiration = (new DateTime())->modify("+900 seconds");
        }

        return $this;
    }

    /**
     * Retrieves the expiration time of the cache item.
     *
     * If the item is a cache miss, this method MAY return either the time at which it expired
     * or the current time if the expiration time is unavailable.
     *
     * @return DateTimeInterface Return the timestamp indicating when this cache item will expire.
     */
    public function getExpiration(): DateTimeInterface
    {
        return $this->expiration;
    }
}
