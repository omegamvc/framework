<?php

declare(strict_types=1);

namespace Omega\Security\Hashing;

use RuntimeException;

interface HashInterface
{
    /**
     * Get information about hash.
     *
     * @return array<string, int|string|bool>
     */
    public function info(string $hash): array;

    /**
     * Verify hash and hashed.
     *
     * @param array<string, int|string|bool> $options
     */
    public function verify(string $value, string $hashedValue, array $options = []): bool;

    /**
     * Hash given string.
     *
     * @param array<string, int|string|bool> $options
     * @throws RuntimeException
     */
    public function make(string $value, array $options = []): string;

    public function isValidAlgorithm(string $hash): bool;
}
