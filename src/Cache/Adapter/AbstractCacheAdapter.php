<?php

/**
 * Part of Omega - Cache Package.
 * php version 8.2
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Cache\Adapter;

/**
 * Abstract cache adapter class.
 *
 * The `AbstractCacheAdapter` class provides a foundation for implementing cache
 * adapters. It implements the methods defined in the CacheAdapterInterface and
 * adds some common properties and behaviors.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Adapter
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
abstract class AbstractCacheAdapter implements CacheAdapterInterface
{
    /**
     * Cacheds data.
     *
     * @var array<string, mixed> Holds an array to store cached data.
     */
    public array $cached = [];

    /**
     * AbstractCacheAdapter class constructor.
     *
     * @param array<string, mixed> $config Holds the configuration options for the cache adapter.
     * @return void
     */
    public function __construct(
        public array $config
    ) {
    }

    /**
     * {@inheritdoc}
     */
    abstract public function has(string $key): bool;

    /**
     * {@inheritdoc}
     */
    abstract public function get(string $key, mixed $default = null): mixed;

    /**
     * {@inheritdoc}
     */
    abstract public function put(string $key, mixed $value, ?int $seconds = null): static;

    /**
     * {@inheritdoc}
     */
    abstract public function forget(string $key): static;

    /**
     * {@inheritdoc}
     */
    abstract public function flush(): static;

    /**
     * Get the config.
     *
     * @return array<string, mixed> Return an array of config.
     */
    public function config(): array
    {
        return $this->config;
    }
}
