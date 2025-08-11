<?php

declare(strict_types=1);

namespace Omega\Security\Hashing;

use function password_get_info;
use function password_hash;
use function password_verify;

use const PASSWORD_DEFAULT;

class DefaultHasher implements HashInterface
{
    public function info(string $hash): array
    {
        return password_get_info($hash);
    }

    public function verify(string $value, string $hashedValue, array $options = []): bool
    {
        return password_verify($value, $hashedValue);
    }

    public function make(string $value, array $options = []): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public function isValidAlgorithm(string $hash): bool
    {
        return 'bcrypt' === $this->info($hash)['algoName'];
    }
}
