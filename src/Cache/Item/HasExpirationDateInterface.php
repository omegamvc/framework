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

namespace Omega\Cache\Item;

use DateTimeInterface;

/**
 * Interface for cache items with expiration dates.
 *
 * This interface defines a contract for cache items that have an associated expiration time.
 * Implementing classes must provide a method to retrieve the expiration timestamp of a stored item,
 * allowing cache pools to determine its validity.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Item
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
interface HasExpirationDateInterface
{
    /**
     * Retrieves the expiration time of a valid cache item.
     *
     * If the cache item is a Cache Miss, this method MAY return either the time at which the item expired
     * or the current time if the expiration time is unavailable.
     *
     * @return DateTimeInterface The timestamp indicating when this cache item will expire.
     */
    public function getExpiration(): DateTimeInterface;
}
