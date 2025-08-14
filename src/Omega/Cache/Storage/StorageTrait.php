<?php

declare(strict_types=1);

namespace Omega\Cache\Storage;

use DateInterval;
use DateTimeInterface;

use function microtime;
use function round;
use function time;

trait StorageTrait
{
    /**
     * Calculate the microtime based on the current time and microtime.
     *
     * @return float
     */
    protected function createMtime(): float
    {
        $currentTime = time();
        $microtime   = microtime(true);

        $fractionalPart = $microtime - $currentTime;

        if ($fractionalPart >= 1) {
            $currentTime += (int) $fractionalPart;
            $fractionalPart -= (int) $fractionalPart;
        }

        $mtime = $currentTime + $fractionalPart;

        return round($mtime, 3);
    }

    /**
     * Get info of storage.
     *
     * @param string $key
     * @return array<string, array{value: mixed, timestamp?: int, mtime?: float}>
     */
    abstract public function getInfo(string $key): array;

    /**
     * @param int|DateInterval|DateTimeInterface|null $ttl
     * @return int
     */
    abstract protected function calculateExpirationTimestamp(int|DateInterval|DateTimeInterface|null $ttl): int;
}
