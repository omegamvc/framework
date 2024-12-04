<?php

/**
 * Part of Omega -  Session Package.
 *
 * @see       https://omegamvc.github.io
 *
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
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
 * Abstract storage class.
 *
 * The `AbstractStorage` class serves as a base class for session storage implementations.
 * It provides a skeletal implementation of the `StorageInterface` methods.
 *
 * @category    Omega
 * @package     Session
 * @subpackage  Storage
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
abstract class AbstractStorage implements StorageInterface
{
    /**
     * {@inheritdoc}
     *
     * @param string $key The session key.
     *
     * @return bool Return true if the session value exists.
     */
    abstract public function has(string $key): bool;

    /**
     * {@inheritdoc}
     *
     * @param string $key     The session key.
     * @param mixed  $default The default value to return if the key is not found.
     *
     * @return mixed Return the session value or the default value if the key is not found.
     */
    abstract public function get(string $key, mixed $default = null): mixed;

    /**
     * {@inheritdoc}
     *
     * @param string $key   The session key.
     * @param mixed  $value The session value.
     *
     * @return $this
     */
    abstract public function put(string $key, mixed $value): static;

    /**
     * {@inheritdoc}
     *
     * @param string $key The session key.
     *
     * @return $this
     */
    abstract public function forget(string $key): static;

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    abstract public function flush(): static;
}
