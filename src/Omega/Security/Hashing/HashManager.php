<?php

declare(strict_types=1);

namespace Omega\Security\Hashing;

use function array_key_exists;

class HashManager implements HashInterface
{
    /** @var array<string, HashInterface> */
    private array $driver = [];

    private HashInterface $defaultDriver;

    public function __construct()
    {
        $this->setDefaultDriver(new DefaultHasher());
    }

    public function setDefaultDriver(HashInterface $driver): self
    {
        $this->defaultDriver = $driver;

        return $this;
    }

    public function setDriver(string $driverName, HashInterface $driver): self
    {
        $this->driver[$driverName] = $driver;

        return $this;
    }

    public function driver(?string $driver = null): HashInterface
    {
        if (array_key_exists($driver, $this->driver)) {
            return $this->driver[$driver];
        }

        return $this->defaultDriver;
    }

    public function info(string $hash): array
    {
        return $this->driver()->info($hash);
    }

    public function make(string $value, array $options = []): string
    {
        return $this->driver()->make($value, $options);
    }

    public function verify(string $value, string $hashedValue, array $options = []): bool
    {
        return $this->driver()->verify($value, $hashedValue, $options);
    }

    public function isValidAlgorithm(string $hash): bool
    {
        return $this->driver()->isValidAlgorithm($hash);
    }
}
