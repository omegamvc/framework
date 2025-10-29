<?php

declare(strict_types=1);

namespace Tests\RateLimiter;

use Omega\Cache\Storage\Memory;
use Omega\RateLimiter\RateLimiter;
use Omega\RateLimiter\RateLimiter\FixedWindow;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FixedWindow::class)]
#[CoversClass(Memory::class)]
#[CoversClass(RateLimiter::class)]
class RateLimiterTest extends TestCase
{
    /** @var RateLimiter  */
    private RateLimiter $rateLimiter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->rateLimiter = new RateLimiter(
            new FixedWindow(
                cache: new Memory(['ttl' => 3600]),
                limit: 1,
                windowSeconds: 60
            )
        );
    }

    /**
     * Test is blocked.
     *
     * @return void
     */
    public function isBlocked(): void
    {
        $this->assertFalse($this->rateLimiter->isBlocked('key', 1, 1));
        $this->rateLimiter->consume('key');
        $this->assertTrue($this->rateLimiter->isBlocked('key', 1, 1));
    }

    /**
     * Test it consume.
     *
     * @return void
     */
    public function testItConsume(): void
    {
        $this->assertEquals(1, $this->rateLimiter->consume('key'));
    }

    /**
     * Test get count left.
     *
     * @return void
     */
    public function testGetCountLeft(): void
    {
        $this->assertEquals(0, $this->rateLimiter->getCount('key'));
        $this->rateLimiter->consume('key');
        $this->assertEquals(1, $this->rateLimiter->getCount('key'));
    }

    /**
     * Test get retry after.
     *
     * @return void
     */
    public function getRetryAfter(): void
    {
        $this->assertGreaterThan(0, $this->rateLimiter->getRetryAfter('key'));
    }

    /**
     * Test it remaining.
     *
     * @return void
     */
    public function testItRemaining(): void
    {
        $this->assertEquals(1, $this->rateLimiter->remaining('key', 1));
        $this->rateLimiter->consume('key');
        $this->assertEquals(0, $this->rateLimiter->remaining('key', 1));
    }

    /**
     * Test it reset.
     *
     * @return void
     */
    public function testItReset(): void
    {
        $this->rateLimiter->consume('key');
        $this->rateLimiter->reset('key');
        $this->assertEquals(0, $this->rateLimiter->getCount('key'));
    }
}
