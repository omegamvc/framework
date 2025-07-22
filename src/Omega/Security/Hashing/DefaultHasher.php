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

use function password_get_info;
use function password_hash;
use function password_verify;

use const PASSWORD_DEFAULT;

/**
 * DefaultHasher provides a basic implementation of the HashInterface using PHP's native password_* functions.
 *
 * This class uses `password_hash`, `password_verify`, and related functions to hash and verify values
 * using the default algorithm defined by the PHP runtime (typically bcrypt).
 *
 * It is suitable for general-purpose hashing needs where secure, widely supported hashing is sufficient,
 * and custom configuration is not required.
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
class DefaultHasher implements HashInterface
{
    /**
     * {@inheritdoc}
     */
    public function info(string $hash): array
    {
        return password_get_info($hash);
    }

    /**
     * {@inheritdoc}
     */
    public function verify(string $value, string $hashedValue, array $options = []): bool
    {
        return password_verify($value, $hashedValue);
    }

    /**
     * {@inheritdoc}
     */
    public function make(string $value, array $options = []): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    /**
     * {@inheritdoc}
     */
    public function isValidAlgorithm(string $hash): bool
    {
        return 'bcrypt' === $this->info($hash)['algoName'];
    }
}
