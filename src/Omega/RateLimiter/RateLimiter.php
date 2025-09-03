<?php

declare(strict_types=1);

namespace Omega\RateLimiter;

class RateLimiter implements RateLimiterInterface
{
    public function __construct(private RateLimiterPolicyInterface $Limiter)
    {
    }

    public function isBlocked(string $key, int $maxAttempts, int|\DateInterval $decay): bool
    {
        return $this->Limiter->peek($key)->isBlocked();
    }

    public function consume(string $key, int $decayMinutes = 1): int
    {
        return $this->Limiter->consume($key, 1)->getConsumed();
    }

    public function getCount(string $key): int
    {
        return $this->Limiter->peek($key)->getConsumed();
    }

    public function getRetryAfter(string $key): int
    {
        $result = $this->Limiter->peek($key);
        if (null === $result->getRetryAfter()) {
            return 0;
        }

        return max(0, $result->getRetryAfter()->getTimestamp() - now()->timestamp);
    }

    public function remaining(string $key, int $maxAttempts): int
    {
        return $this->Limiter->peek($key)->getRemaining();
    }

    public function reset(string $key): void
    {
        $this->Limiter->reset($key);
    }
}
