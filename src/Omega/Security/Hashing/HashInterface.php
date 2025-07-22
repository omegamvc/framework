<?php

/**
 * Part of Omega - Security Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Security\Hashing;

use RuntimeException;

/**
 * Defines the contract for hashing operations.
 *
 * This interface standardizes the behavior of hash-based algorithms,
 * including the ability to generate, verify, and inspect hashed values.
 * It is intended to support various hashing strategies such as bcrypt, Argon2, etc.
 *
 * Implementations must ensure that hashing operations are secure,
 * resistant to timing attacks, and compliant with current best practices.
 *
 * @category   Omega
 * @package    Security
 * @subpackage Hashing
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
interface HashInterface
{
    /**
     * Retrieve metadata about a hashed value.
     *
     * This can include information such as the algorithm used,
     * cost factors, and whether the hash needs to be rehashed.
     *
     * @param string $hash The hashed value to inspect.
     * @return array<string, int|string|bool> An associative array of hash metadata.
     */
    public function info(string $hash): array;

    /**
     * Verify that a plain-text value matches a given hash.
     *
     * Optionally accepts hashing options for advanced configuration,
     * depending on the underlying algorithm.
     *
     * @param string $value The plain-text value to verify.
     * @param string $hashedValue The hashed string to compare against.
     * @param array<string, int|string|bool> $options Optional hashing options (e.g. cost).
     * @return bool True if the value matches the hash, false otherwise.
     */
    public function verify(string $value, string $hashedValue, array $options = []): bool;

    /**
     * Hash a plain-text value using the configured algorithm.
     *
     * The hashing process can be influenced by the provided options,
     * such as cost or memory usage, depending on the implementation.
     *
     * @param string $value The plain-text string to hash.
     * @param array<string, int|string|bool> $options Optional hashing options.
     * @return string The resulting hashed value.
     * @throws RuntimeException If the hashing process fails.
     */
    public function make(string $value, array $options = []): string;

    /**
     * Determine whether a hash is valid and compatible with the algorithm.
     *
     * This can be used to validate legacy or user-provided hashes before attempting operations.
     *
     * @param string $hash The hash string to validate.
     * @return bool True if the hash is valid for the algorithm, false otherwise.
     */
    public function isValidAlgorithm(string $hash): bool;
}
