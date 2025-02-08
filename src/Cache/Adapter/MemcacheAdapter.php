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

use Memcached;
use Omega\Cache\Exception\UnsupportedAdapterException;

use function is_int;
use function is_numeric;
use function time;

/**
 * Memcache adapter class.
 *
 * The `MemcachedAdapter` class implements a cache adapter that uses the Memcached
 * extension to interact with a Memcached server. It extends the AbstractCacheAdapter
 * class and provides methods to check, retrieve, store, and manage cached data using
 * Memcached.
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
class MemcacheAdapter extends AbstractCacheAdapter
{
    /**
     * Memcached object.
     *
     * @var Memcached Holds an object of memcached.
     */
    private Memcached $memcache;

    /**
     * MemcacheAdapter class constructor.
     *
     * Initializes the MemcacheAdapter with configuration options and connects to
     * the Memcached server.
     *
     * @param array<string, mixed> $config Holds an array of configuration options.
     * @return void
     * @throws UnsupportedAdapterException if the Memcached extension is not enabled or
     *                                     installed, preventing the caching functionality
     *                                     from being used.
     */
    public function __construct(array $config)
    {
        if (!extension_loaded('memcached')) {
            throw new UnsupportedAdapterException(
                'Memcached extension is not enabled. Please make sure to install the Memcached extension'
                . 'to enable caching functionality and improve performance.'
                . 'You can install it using your system\'s package manager or '
                . 'by following the instructions provided in the Memcached documentation.'
            );
        }

        parent::__construct($config);

        $host = isset($config['host']) && is_string($config['host']) ? $config['host'] : '';
        $port = isset($config['port']) && is_int($config['port']) ? $config['port'] : 0;


        $this->memcache = new Memcached();
        $this->memcache->addServer($host, $port);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return $this->memcache->get($key) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($value = $this->memcache->get($key)) {
            return $value;
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $key, mixed $value, ?int $seconds = null): static
    {
        if (!is_int($seconds)) {
            $seconds = isset($this->config['seconds']) && is_numeric($this->config['seconds'])
                ? $this->config['seconds']
                : 0;
        }

        $this->memcache->set($key, $value, time() + (int)$seconds);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function forget(string $key): static
    {
        $this->memcache->delete($key);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): static
    {
        $this->memcache->flush();

        return $this;
    }
}
