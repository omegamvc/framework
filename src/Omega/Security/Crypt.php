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

use Exception;
use Random\RandomException;

use function base64_decode;
use function base64_encode;
use function count;
use function explode;
use function hash;
use function openssl_decrypt;
use function openssl_encrypt;
use function random_bytes;

use const OPENSSL_RAW_DATA;

/**
 * Provides symmetric encryption and decryption using OpenSSL.
 *
 * The Crypt class offers functionality to encrypt and decrypt plain text using
 * symmetric algorithms such as AES. It uses a passphrase to derive a secure hash
 * and randomly generates an initialization vector (IV) upon construction.
 *
 * The algorithm format must follow the pattern: "<cipher_algo>;<iv_length>", e.g. "aes-256-cbc;16".
 *
 * Note: The IV is randomly generated per instance, making the class non-repeatable
 * for decryption across different instances unless the same IV is shared externally.
 *
 * @category  Omega
 * @package   Security
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class Crypt
{
    /** @var string The OpenSSL cipher algorithm name (e.g. 'aes-256-cbc'). */
    private string $cipherAlgo;

    /**
     * The randomly generated initialization vector (IV) for encryption.
     *
     * Length is determined by the algorithm's IV size specified at construction.
     *
     * @var string
     */
    private string $iv;

    /** @var string The SHA-256 hashed value of the passphrase used as the encryption key. */
    private string $hash;

    /**
     * Create a new Crypt instance with the given passphrase and cipher configuration.
     *
     * Parses the algorithm string, generates a random IV, and hashes the passphrase.
     *
     * @param string $passphrase   The secret key used for hashing and encryption.
     * @param string $cipherAlgo   Cipher definition (e.g. 'aes-256-cbc;16').
     * @throws RandomException If IV generation fails.
     * @throws Exception If the algorithm format is invalid.
     */
    public function __construct(string $passphrase, string $cipherAlgo)
    {
        [$this->cipherAlgo, $chars] = $this->algoParse($cipherAlgo);
        $this->iv                   = random_bytes($chars);
        $this->hash                 = $this->hash($passphrase);
    }

    /**
     * Parse the cipher algorithm string and extract its components.
     *
     * The expected format is "<cipher_name>;<iv_length>".
     *
     * @param string $chiperAlgo The full algorithm configuration string.
     * @return array{0: string, 1: int} Tuple containing cipher name and IV length.
     * @throws Exception If the algorithm string is invalid or malformed.
     */
    private function algoParse(string $chiperAlgo): array
    {
        $parse = explode(';', $chiperAlgo);

        if (count($parse) < 2) {
            throw new Exception('Chiper algo must provide chars length');
        }

        return [$parse[0], (int) $parse[1]];
    }

    /**
     * Hash the given passphrase using SHA-256.
     *
     * The hash is returned in binary format (raw output).
     *
     * @param string $passphrase The passphrase to hash.
     * @return string Binary hash of the passphrase.
     */
    public function hash(string $passphrase): string
    {
        return hash('sha256', $passphrase, true);
    }

    /**
     * Encrypt the given plain text string.
     *
     * Optionally a different passphrase can be used; otherwise, the one provided
     * during construction will be used.
     *
     * @param string $plainText     The plain text to encrypt.
     * @param string|null $passphrase Optional custom passphrase.
     * @return string The base64-encoded encrypted string.
     */
    public function encrypt(string $plainText, ?string $passphrase = null): string
    {
        $hash = $passphrase === null ? null : $this->hash($passphrase);

        return base64_encode(
            openssl_encrypt(
                $plainText,
                $this->cipherAlgo,
                $hash ?? $this->hash,
                OPENSSL_RAW_DATA,
                $this->iv
            )
        );
    }

    /**
     * Decrypt the given encrypted string.
     *
     * Optionally a different passphrase can be used; otherwise, the one provided
     * during construction will be used.
     *
     * @param string $encrypted     The base64-encoded encrypted string.
     * @param string|null $passphrase Optional custom passphrase.
     * @return string The decrypted plain text.
     */
    public function decrypt(string $encrypted, ?string $passphrase = null): string
    {
        $hash = $passphrase === null ? null : $this->hash($passphrase);

        return openssl_decrypt(
            base64_decode($encrypted),
            $this->cipherAlgo,
            $hash ?? $this->hash,
            OPENSSL_RAW_DATA,
            $this->iv
        );
    }
}
