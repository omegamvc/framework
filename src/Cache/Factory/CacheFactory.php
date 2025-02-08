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

namespace Omega\Cache\Factory;

use Omega\Cache\Adapter\CacheAdapterInterface;
use Omega\Cache\Adapter\FileAdapter;
use Omega\Cache\Adapter\MemcacheAdapter;
use Omega\Cache\Adapter\MemoryAdapter;
use Omega\Cache\Exception\UnsupportedAdapterException;

/**
 * Cache factory class.
 *
 * The `CacheFactory` class is responsible for registering and creating cache
 * drivers based on configurations. It acts as a factory for different cache
 * drivers and provides a flexible way to connect to various caching systems.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Factory
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class CacheFactory implements CacheFactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @param array<string, mixed>|null $config Holds an optional configuration array that may be used to influence
     *                                          the creation of the object. If no configuration is provided, default
     *                                          settings may be applied.
     * @return CacheAdapterInterface Return the created object or value. The return type is flexible, allowing for any
     *                               type to be returned, depending on the implementation.
     * @throws UnsupportedAdapterException if the adapter is not defined.
     */
    public function create(?array $config = null): CacheAdapterInterface
    {
        if (!isset($config['type'])) {
            throw new UnsupportedAdapterException(
                'Type is not defined.'
            );
        }

        return match ($config['type']) {
            'file'     => new FileAdapter($config),
            'memcache' => new MemcacheAdapter($config),
            'memory'   => new MemoryAdapter($config),
            default    => throw new UnsupportedAdapterException('Unrecognized type.')
        };
    }
}
