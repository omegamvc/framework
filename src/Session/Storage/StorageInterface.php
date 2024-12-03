<?php

/**
 * Part of Omega CMS -  Session Package.
 *
 * @see       https://omegacms.github.io
 *
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Session\Storage;

/**
 * Storage interface.
 *
 * The `StorageInterface` defines the contract for session storage implementations.
 * It provides methods for checking, retrieving, storing, and removing session values.
 *
 * @category    Omega
 * @package     Session
 * @subpackage  Storage
 *
 * @see        https://omegacms.github.io
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
interface StorageInterface
{
    /**
     * Check if a session value exists.
     *
     * @param string $key The session key.
     *
     * @return bool Return true if the session value exists.
     */
    public function has(string $key): bool;

    /**
     * Get a session value.
     *
     * @param string $key     The session key.
     * @param mixed  $default The default value to return if the key is not found.
     *
     * @return mixed Return the session value or the default value if the key is not found.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Store a value in the session.
     *
     * @param string $key   The session key.
     * @param mixed  $value The session value.
     *
     * @return $this
     */
    public function put(string $key, mixed $value): static;

    /**
     * Remove a single session value.
     *
     * @param string $key The session key.
     *
     * @return $this
     */
    public function forget(string $key): static;

    /**
     * Remove all session values.
     *
     * @return $this
     */
    public function flush(): static;
}
