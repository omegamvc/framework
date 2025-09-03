<?php

declare(strict_types=1);

namespace Omega\RateLimiter;

use Omega\Cache\CacheInterface;

use Omega\RateLimiter\RateLimiter\FixedWindow;
use Omega\RateLimiter\RateLimiter\NoLimiter;

class RateLimiterFactory
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function createFixedWindow(int $limit, int $windowSeconds): RateLimiterInterface
    {
        return new RateLimiter(
            new FixedWindow(
                cache: $this->cache,
                limit: $limit,
                windowSeconds: $windowSeconds,
            )
        );
    }

    public function createNoLimiter(): RateLimiterInterface
    {
        return new RateLimiter(
            new NoLimiter()
        );
    }
}
