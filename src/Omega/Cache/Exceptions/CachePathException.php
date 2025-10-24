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
 * Exception thrown when the cache directory cannot be created or is not writable.
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
class CachePathException extends AbstractCacheException
{
    /**
     * Create a new CachePathException instance.
     *
     * @param string $path The path to the cache directory that could not be created.
     */
    public function __construct(string $path)
    {
        parent::__construct(
            sprintf(
                'The cache directory "%s" could not be created or is not writable. '
                . 'Please ensure the path exists and has proper permissions.',
                $path
            )
        );
    }
}
