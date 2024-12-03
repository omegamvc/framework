<?php

/**
 * Part of Omega CMS - Cache Package.
 * php version 8.3
 *
 * @link      https://omegacms.github.io
 * @author    Adriano Giovannini <omegacms@outlook.com>
 * @copyright Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
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
 * @link       https://omegacms.github.io
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
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
        return isset($this->cached[$key]) && $this->cached[$key]['expires'] > time();
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->has($key)) {
            return $this->cached[$key]['value'];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $key, mixed $value, ?int $seconds = null): static
    {
        if (!is_int($seconds)) {
            $seconds = (int)$this->config['seconds'];
        }

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
