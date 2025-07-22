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

namespace Omega\Security;

use Random\RandomException;

use function function_exists;

/**
 * Cryptography helper functions.
 *
 * This file provides simple and convenient helper functions for performing
 * symmetric encryption and decryption using the `Crypt` class.
 *
 * These functions are particularly useful for quickly securing or retrieving
 * sensitive information with minimal boilerplate, supporting optional passphrases
 * and customizable encryption algorithms.
 *
 * Functions:
 * - encrypt(string $plain_text, ?string $passphrase = null, string $algo = Algo::AES_256_CBC): string
 * - decrypt(string $encrypted, ?string $passphrase = null, string $algo = Algo::AES_256_CBC): string
 *
 * Requires the `Crypt` class and `Algo` constants to be defined and available in the context.
 *
 * @category  Omega
 * @package   Security
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

if (!function_exists('encrypt')) {
    /**
     * Encrypt the given plain text using the specified algorithm and optional passphrase.
     *
     * Internally creates a new instance of the `Crypt` class with the given passphrase and
     * algorithm, and uses it to securely encrypt the provided plain text string.
     *
     * @param string      $plain_text The plain text string to encrypt.
     * @param string|null $passphrase Optional passphrase used for encryption (default: null).
     * @param string      $algo       The encryption algorithm to use (default: AES-256-CBC).
     * @return string The encrypted, base64-encoded string.
     * @throws RandomException If a cryptographic random key or IV cannot be generated.
     */
    function encrypt(string $plain_text, ?string $passphrase = null, string $algo = Algo::AES_256_CBC): string
    {
        return (new Crypt($passphrase, $algo))->encrypt($plain_text);
    }
}

if (!function_exists('decrypt')) {
    /**
     * Decrypt the given encrypted string using the specified algorithm and optional passphrase.
     *
     * Internally creates a new instance of the `Crypt` class with the given passphrase and
     * algorithm, and uses it to decrypt the provided encrypted string.
     *
     * @param string      $encrypted  The encrypted, base64-encoded string to decrypt.
     * @param string|null $passphrase Optional passphrase used during encryption (default: null).
     * @param string      $algo       The encryption algorithm used to encrypt the string (default: AES-256-CBC).
     * @return string The decrypted plain text string.
     * @throws RandomException If decryption fails or cryptographic parameters are invalid.
     */
    function decrypt(string $encrypted, ?string $passphrase = null, string $algo = Algo::AES_256_CBC): string
    {
        return (new Crypt($passphrase, $algo))->decrypt($encrypted);
    }
}
