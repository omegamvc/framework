<?php

/**
 * Part of Omega -  Session Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Session\Storage;

/**
 * Abstract storage class.
 *
 * The `AbstractStorage` class serves as a base class for session storage implementations.
 * It provides a skeletal implementation of the `StorageInterface` methods.
 *
 * @category   Omega
 * @package    Session
 * @subpackage Storage
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
abstract class AbstractStorage implements StorageInterface
{
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
    abstract public function put(string $key, mixed $value): static;

    /**
     * {@inheritdoc}
     */
    abstract public function forget(string $key): static;

    /**
     * {@inheritdoc}
     */
    abstract public function flush(): static;
}
