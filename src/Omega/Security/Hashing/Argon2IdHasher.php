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

use function is_string;
use function password_hash;

use const PASSWORD_ARGON2ID;

/**
 * Argon2IdHasher provides an implementation of the Argon2id password hashing algorithm.
 *
 * This class extends ArgonHasher and uses the Argon2id variant, which combines
 * the features of Argon2i and Argon2d for enhanced resistance against side-channel
 * and GPU cracking attacks. It supports configurable memory cost, time cost, and
 * parallelism parameters.
 *
 * It throws a RuntimeException if Argon2id hashing is not supported by the PHP environment.
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
class Argon2IdHasher extends ArgonHasher implements HashInterface
{
    /**
     * {@inheritdoc}
     */
    public function make(string $value, array $options = []): string
    {
        $hash = @password_hash($value, PASSWORD_ARGON2ID, [
            'memory_cost' => $options['memory'] ?? $this->memory,
            'time_cost'   => $options['time'] ?? $this->time,
            'threads'     => $options['threads'] ?? $this->threads,
        ]);

        if (!is_string($hash)) {
            throw new RuntimeException(PASSWORD_ARGON2ID . ' hashing not supported.');
        }

        return $hash;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidAlgorithm(string $hash): bool
    {
        return 'argon2id' === $this->info($hash)['algoName'];
    }
}
