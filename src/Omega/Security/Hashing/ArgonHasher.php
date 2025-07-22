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

use const PASSWORD_ARGON2I;

/**
 * ArgonHasher provides an implementation of the Argon2i password hashing algorithm.
 *
 * This class extends DefaultHasher and offers configurable options for memory cost,
 * time cost, and parallelism (threads). It is suitable for applications that require
 * strong password hashing with tunable performance and security settings.
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
class ArgonHasher extends DefaultHasher implements HashInterface
{
    /**
     * The amount of memory (in kilobytes) to be used by the Argon2 algorithm.
     * Default is 1024 KB.
     *
     * @var int
     */
    protected int $memory = 1024;

    /**
     * The number of iterations (time cost) for the Argon2 algorithm.
     * Default is 2.
     *
     * @var int
     */
    protected int $time = 2;

    /**
     * The number of parallel threads to be used by the Argon2 algorithm.
     * Default is 2.
     *
     * @var int
     */
    protected int $threads = 2;

    /**
     * Set the amount of memory (in kilobytes) to be used by the Argon2 algorithm.
     *
     * @param int $memory The memory cost in KB (e.g., 1024, 2048, 4096).
     * @return self
     */
    public function setMemory(int $memory): self
    {
        $this->memory = $memory;

        return $this;
    }

    /**
     * Set the number of iterations (time cost) for the Argon2 algorithm.
     *
     * @param int $time The time cost (e.g., 2, 3, 4).
     * @return self
     */
    public function setTime(int $time): self
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Set the number of threads (parallelism) for the Argon2 algorithm.
     *
     * @param int $threads The number of threads to use (e.g., 1, 2, 4).
     * @return self
     */
    public function setThreads(int $threads): self
    {
        $this->threads = $threads;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function make(string $value, array $options = []): string
    {
        $hash = @password_hash($value, PASSWORD_ARGON2I, [
            'memory_cost' => $options['memory'] ?? $this->memory,
            'time_cost'   => $options['time'] ?? $this->time,
            'threads'     => $options['threads'] ?? $this->threads,
        ]);

        if (!is_string($hash)) {
            throw new RuntimeException(PASSWORD_ARGON2I . ' hashing not supported.');
        }

        return $hash;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidAlgorithm(string $hash): bool
    {
        return 'argon2i' === $this->info($hash)['algoName'];
    }
}
