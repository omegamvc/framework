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

namespace Omega\Cache\Exceptions;

use function sprintf;

/**
 * Exception thrown when attempting to resolve a cache storage driver
 * that is unknown or has not been registered.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class UnknownStorageException extends AbstractCacheException
{
    /**
     * Create a new UnknownStorageException instance.
     *
     * @param string $driverName The name of the storage driver that could not be resolved.
     */
    public function __construct(string $driverName)
    {
        parent::__construct(
            sprintf(
                'The cache storage driver "%s" could not be resolved or is not registered.',
                $driverName
            )
        );
    }
}
