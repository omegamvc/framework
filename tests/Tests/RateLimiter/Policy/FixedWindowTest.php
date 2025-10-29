<?php

declare(strict_types=1);

namespace Tests\RateLimiter\Policy;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use Omega\Cache\Storage\Memory;
use Omega\RateLimiter\RateLimiter\FixedWindow;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Time\now;

#[CoversClass(FixedWindow::class)]
#[CoversClass(Memory::class)]
class FixedWindowTest extends TestCase
{
    /** @var Memory */
    private Memory $cache;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = new Memory(['ttl' => 3600]);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cache->clear();
    }

    /**
     * Test it can consume tokens within the limit.
     *
     * @return void
     */
    public function itCanConsumeTokensWithinTheLimit(): void
    {
        $limiter   = new FixedWindow($this->cache, 5, 60);
        $rateLimit = $limiter->consume('test_key');

        $this->assertFalse($rateLimit->isBlocked());
        $this->assertEquals(1, $rateLimit->getConsumed());
        $this->assertEquals(4, $rateLimit->getRemaining());
    }

    /**
     * Test it blocks when consuming tokens exceeds the limit.
     *
     * @return void
     */
    public function testItBlocksWhenConsumingTokensExceedsTheLimit(): void
    {
        $limiter = new FixedWindow($this->cache, 5, 60);

        for ($i = 0; $i < 5; $i++) {
            $limiter->consume('test_key');
        }

        $rateLimit = $limiter->consume('test_key');

        $this->assertTrue($rateLimit->isBlocked());
        $this->assertEquals(5, $rateLimit->getConsumed());
        $this->assertEquals(0, $rateLimit->getRemaining());
    }

    /**
     * Test it can peek at the rate limit status.
     *
     * @return void
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testItCanPeekAtTheRateLimitStatus(): void
    {
        $limiter = new FixedWindow($this->cache, 5, 60);

        $this->cache->set('test_key:fw:' . floor(now()->timestamp / 60), 3);

        $rateLimit = $limiter->peek('test_key');

        $this->assertFalse($rateLimit->isBlocked());
        $this->assertEquals(3, $rateLimit->getConsumed());
        $this->assertEquals(2, $rateLimit->getRemaining());
    }

    /**
     * Test it can reset the rate limit.
     *
     * @return void
     */
    public function testItCanResetTheRateLimit(): void
    {
        $limiter = new FixedWindow($this->cache, 5, 60);

        $limiter->consume('test_key');
        $limiter->reset('test_key');

        $rateLimit = $limiter->peek('test_key');

        $this->assertEquals(0, $rateLimit->getConsumed());
    }
}
