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

use function password_hash;

use const PASSWORD_BCRYPT;

/**
 * BcryptHasher provides a specific implementation of password hashing using the bcrypt algorithm.
 *
 * This class extends DefaultHasher and allows configuration of the number of hashing rounds (cost factor).
 * It is ideal when you need more control over the security strength of the hash operation.
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
class BcryptHasher extends DefaultHasher implements HashInterface
{
    /**
     * The number of rounds (cost factor) to be used by the bcrypt algorithm.
     *
     * Higher values increase computational time, improving resistance against brute-force attacks.
     * The default value is 12.
     *
     * @var int
     */
    protected int $rounds = 12;

    /**
     * Set the number of rounds for the bcrypt hashing algorithm.
     *
     * This allows tuning the computational cost of hashing.
     * Higher values increase security at the cost of performance.
     *
     * @param int $rounds The cost factor (typically between 10 and 14).
     * @return self
     */
    public function setRounds(int $rounds): self
    {
        $this->rounds = $rounds;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function make(string $value, array $options = []): string
    {
        return password_hash($value, PASSWORD_BCRYPT, [
            'cost' => $options['rounds'] ?? $this->rounds,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function isValidAlgorithm(string $hash): bool
    {
        return 'bcrypt' === $this->info($hash)['algoName'];
    }
}
