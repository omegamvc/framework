<?php

/**
 * Part of Omega - Container Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Container;

/**
 * Interface for extended container behavior with alias support and state control.
 *
 * This interface defines additional methods for a dependency injection container,
 * allowing the registration of aliases for services or class names, recursive alias resolution,
 * and complete state flushing. It is designed to extend standard container capabilities
 * without interfering with existing PSR-11 compliance.
 *
 * @category  Omega
 * @package   Container
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
interface ContainerInterface
{
    /**
     * Registers an alias for a container entry.
     *
     * This allows an abstract or service name to be referenced by an alternative alias.
     *
     * @param string $abstract The original service or class name.
     * @param string $alias The alias to associate with the original entry.
     * @return void
     */
    public function alias(string $abstract, string $alias): void;

    /**
     * Resolves alias recursively for a given abstract name.
     *
     * If an alias is defined, returns the original name.
     * If no alias is found, returns the input unchanged.
     *
     * @param string $abstract The alias or original entry name.
     * @return string The resolved original entry name.
     */
    public function getAlias(string $abstract): string;

    /**
     * Flushes the container state.
     *
     * This clears all alias definitions and resets resolved entries.
     *
     * @return void
     */
    public function flush(): void;
}
