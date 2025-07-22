<?php

/**
 * Part of Omega - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\Security\Hashing\HashInterface;
use Omega\Security\Hashing\HashManager;

/**
 * Facade for the hashing service.
 *
 * Provides a static interface for creating, verifying, and inspecting hashed values
 * using the configured hashing drivers. This abstraction allows for flexibility
 * in choosing and configuring the hashing algorithm (e.g., bcrypt, Argon2).
 *
 * Example:
 * ```php
 * $hash = Hash::make('password');
 * if (Hash::verify('password', $hash)) {
 *     // Password is valid
 * }
 * ```
 * @category   Omega
 * @package    Support
 * @subpackage Facades
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 *
 * @method static self setDefaultDriver(HashInterface $driver) Set the default hashing driver.
 * @method static self setDriver(string $driverName, HashInterface $driver) Register a named hashing driver.
 * @method static HashInterface driver(?string $driver = null) Retrieve the specified (or default) hashing driver.
 * @method static array info(string $hashedValue) Retrieve information about the given hash.
 * @method static string make(string $value, array $options = []) Hash a plain-text string.
 * @method static bool verify(string $value, string $hashedValue, array $options = []) Check if the given plain value matches the hash.
 * @method static bool sValidAlgorithm(string $hash) Check if the hash was created using a supported algorithm.
 */
class Hash extends Facade
{
    /**
     * Get the service accessor key for the hash manager.
     *
     * @return string The hash manager service accessor key.
     */
    protected static function getAccessor(): string
    {
        return HashManager::class;
    }
}
