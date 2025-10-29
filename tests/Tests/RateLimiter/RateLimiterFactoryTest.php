<?php

declare(strict_types=1);

namespace Tests\RateLimiter;

use Omega\Cache\Storage\Memory;
use Omega\RateLimiter\RateLimiterFactory;
use Omega\RateLimiter\RateLimiterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Memory::class)]
#[CoversClass(RateLimiterFactory::class)]
class RateLimiterFactoryTest extends TestCase
{
    /**
     * Test it can create rate limiter.
     *
     * @return void
     */
    public function testItCanCreateRateLimiter(): void
    {
        $factory = new RateLimiterFactory(new Memory(['ttl' => 3600]));

        $this->assertInstanceOf(
            RateLimiterInterface::class,
            $factory->createFixedWindow(10, 60)
        );

        $this->assertInstanceOf(
            RateLimiterInterface::class,
            $factory->createNoLimiter()
        );
    }
}
