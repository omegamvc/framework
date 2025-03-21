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

use Memcached as M;
use Redis as R;
use Omega\Cache\CacheItemPoolInterface;
use Omega\Cache\Adapter\Apcu;
use Omega\Cache\Adapter\File;
use Omega\Cache\Adapter\Memcached;
use Omega\Cache\Adapter\Memory;
use Omega\Cache\Adapter\Redis;
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
     * @return CacheItemPoolInterface Return the created object or value. The return type is flexible, allowing for any
     *                               type to be returned, depending on the implementation.
     * @throws UnsupportedAdapterException if the adapter is not defined.
     */
    public function create(?array $config = null): CacheItemPoolInterface
    {
        if (!isset($config['type'])) {
            throw new UnsupportedAdapterException('Type is not defined.');
        }

        return match ($config['type']) {
            'apcu'      => new Apcu($config),
            'file'      => new File($config),
            'memory'    => new Memory($config),
            'memcached' => $this->createMemcached($config),
            'redis'     => $this->createRedis($config),
            default     => throw new UnsupportedAdapterException('Unrecognized type.')
        };
    }

    /**
     * {@inheritdoc}
     */
    public function createMemcached(array $config): Memcached
    {
        $memcached = new M();

        /** @var string $host */
        $host = config('cache.memcached.host');

        /** @var int $port */
        $port = config('cache.memcached.port');

        $memcached->addServer($host, $port);

        return new Memcached($memcached, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function createRedis(array $config): Redis
    {
        $redis = new R();

        /** @var string $host */
        $host = config('cache.redis.host');

        /** @var int $port */
        $port = config('cache.redis.port');

        $redis->connect($host, $port);

        return new Redis($redis, $config);
    }
}
