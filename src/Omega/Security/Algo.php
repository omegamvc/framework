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

/**
 * List of supported encryption algorithms and their configurations.
 *
 * This class defines the available symmetric encryption algorithms that can be used
 * by the Crypt service. Each constant includes both the OpenSSL cipher name and the
 * required initialization vector (IV) length, separated by a semicolon.
 *
 * Format: "<cipher_name>;<iv_length>"
 *
 * Example: 'aes-256-cbc;16' → AES-256-CBC with 16 bytes IV
 *
 * @final
 * @category  Omega
 * @package   Security
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
final class Algo
{
    /**
     * AES-128-CBC algorithm configuration with 16-byte IV.
     *
     * Uses the AES cipher with a 128-bit key in CBC mode.
     * IV length must be exactly 16 bytes for this algorithm.
     *
     * Format: 'aes-128-cbc;16'
     */
    public const string AES_128_CBC = 'aes-128-cbc;16';

    /**
     * AES-256-CBC algorithm configuration with 16-byte IV.
     *
     * Uses the AES cipher with a 256-bit key in CBC mode.
     * IV length must be exactly 16 bytes for this algorithm.
     *
     * Format: 'aes-256-cbc;16'
     */
    public const string AES_256_CBC = 'aes-256-cbc;16';
}
