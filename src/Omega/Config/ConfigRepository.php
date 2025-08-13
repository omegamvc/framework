<?php

declare(strict_types=1);

namespace Omega\Config;

use ArrayAccess;

use function array_key_exists;

/**
 * @implements ArrayAccess<string, mixed>
 */
class ConfigRepository implements ArrayAccess
{
    /**
     * Create new config using array.
     *
     * @param array<string, mixed> $config
     */
    public function __construct(protected array $config = [])
    {
    }

    /**
     * Checks if the given key or index exists in the config.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->config);
    }

    /**
     * Get config.
     *
     * @param string     $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Set new or create config.
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    /**
     * Push value in an array items.
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function push(string $key, mixed $value): void
    {
        $array   = $this->get($key, []);
        $array[] = $value;
        $this->set($key, $array);
    }

    /**
     * Convert back to array.
     *
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        return $this->config;
    }

    /**
     * Checks if the given key or index exists in the config.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Get config.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Set new or create config.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Unset or set to null.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->set($offset, null);
    }
}
