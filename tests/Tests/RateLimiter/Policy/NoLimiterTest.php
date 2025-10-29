<?php

declare(strict_types=1);

namespace Tests\RateLimiter\Policy;

use Omega\RateLimiter\RateLimiter\NoLimiter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use const PHP_INT_MAX;

#[CoversClass(NoLimiter::class)]
class NoLimiterTest extends TestCase
{
    /**
     * Test it can consume tokens within the limit.
     *
     * @return void
     */
    public function testItCanConsumeTokensWithinTheLimit(): void
    {
        $limiter   = new NoLimiter();
        $rateLimit = $limiter->consume('test_key');

        $this->assertFalse($rateLimit->isBlocked());
        $this->assertEquals(0, $rateLimit->getConsumed());
        $this->assertEquals(PHP_INT_MAX, $rateLimit->getRemaining());
    }

    /**
     * Test it never blocks when consuming tokens exceeds the limit.
     *
     * @return void
     */
    public function testItNeverBlocksWhenConsumingTokensExceedsTheLimit(): void
    {
        $limiter = new NoLimiter();

        for ($i = 0; $i < 5; $i++) {
            $limiter->consume('test_key');
        }

        $rateLimit = $limiter->consume('test_key');

        $this->assertFalse($rateLimit->isBlocked());
        $this->assertEquals(0, $rateLimit->getConsumed());
        $this->assertEquals(PHP_INT_MAX, $rateLimit->getRemaining());
    }

    /**
     * Test it can reset the rate limiter.
     *
     * @return void
     */
    public function testItCanResetTheRateLimit(): void
    {
        $limiter = new NoLimiter();

        $limiter->consume('test_key');
        $limiter->reset('test_key');

        $rateLimit = $limiter->peek('test_key');

        $this->assertEquals(0, $rateLimit->getConsumed());
    }
}
