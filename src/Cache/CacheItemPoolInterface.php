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

namespace Omega\Cache;

use Psr\Cache\CacheItemPoolInterface as PsrCacheItemPoolInterface;

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
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
interface CacheItemPoolInterface extends PsrCacheItemPoolInterface
{
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
