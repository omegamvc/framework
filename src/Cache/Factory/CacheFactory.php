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

use Memcached;
use Redis;
use Omega\Cache\CacheItemPoolInterface;
use Omega\Cache\Adapter\FileAdapter;
use Omega\Cache\Adapter\MemcachedAdapter;
use Omega\Cache\Adapter\MemoryAdapter;
use Omega\Cache\Adapter\RedisAdapter;
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
/**class CacheFactory implements CacheFactoryInterface
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
     * /
    public function create(?array $config = null): CacheAdapterInterface
    {
        if (!isset($config['type'])) {
            throw new UnsupportedAdapterException(
                'Type is not defined.'
            );
        }

        return match ($config['type']) {
            //'file'      => new FileAdapter($config),
            'memcached' => new MemcachedAdapter($config),
            //'memory'    => new MemoryAdapter($config),
            default     => throw new UnsupportedAdapterException('Unrecognized type.')
        };
    }
}*/

class CacheFactory implements CacheFactoryInterface
{
    public function create(?array $config = null): CacheItemPoolInterface
    {
        if (!isset($config['type'])) {
            throw new UnsupportedAdapterException('Type is not defined.');
        }

        return match ($config['type']) {
            'file'      => new FileAdapter($config),
            'memory'    => new MemoryAdapter($config),
            'memcached' => $this->createMemcachedAdapter($config),
            'redis'     => $this->createRedisAdapter($config),
            default     => throw new UnsupportedAdapterException('Unrecognized type.')
        };
    }

    /**
     * {@inheritdoc}
     */
    public function createMemcachedAdapter(array $config): MemcachedAdapter
    {
        $memcached = new Memcached();

        $host = $config['host'];
        $port = (int) $config['port'];

        $memcached->addServer($host, $port);

        return new MemcachedAdapter($memcached, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function createRedisAdapter(array $config): RedisAdapter
    {
        $redis = new Redis();

        $host = $config['host'];
        $port = (int) $config['port'];

        $redis->connect($host, $port);

        return new RedisAdapter($redis, $config);
    }
}
