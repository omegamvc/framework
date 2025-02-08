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

use function is_int;
use function time;

/**
 * Memory adapter class.
 *
 * The `MemoryAdapter` class implements a cache adapter that stores cached data in
 * memory. It extends the AbstractCacheAdapter class and provides methods to check,
 * retrieve, store, and manage cached data in memory.
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
class MemoryAdapter extends AbstractCacheAdapter
{
    /**
     * MemoryAdapter class constructor.
     *
     * Initializes the MemoryAdapter with configuration options.
     *
     * @param array<string, mixed> $config Holds an array of configuration options.
     * @return void
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return isset($this->cached[$key])
            && is_array($this->cached[$key])
            && isset($this->cached[$key]['expires'])
            && $this->cached[$key]['expires'] > time();
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->has($key)) {
            $cachedData = $this->cached[$key] ?? null;
            if (is_array($cachedData) && isset($cachedData['value'])) {
                return $cachedData['value'];
            }
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $key, mixed $value, ?int $seconds = null): static
    {
        $seconds = $seconds ?? (isset($this->config['seconds']) && is_int($this->config['seconds']))
            ? $seconds = $this->config['seconds']
            : 0;

        $this->cached[$key] = [
            'value'   => $value,
            'expires' => time() + $seconds,
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function forget(string $key): static
    {
        unset($this->cached[$key]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): static
    {
        $this->cached = [];

        return $this;
    }
}
