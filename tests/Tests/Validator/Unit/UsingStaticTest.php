<?php

declare(strict_types=1);

namespace Tests\Validator\Unit;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for static creation of Validator instance.
 */
#[CoversClass(Validator::class)]
class UsingStaticTest extends TestCase
{
    /**
     * Test static creation of Validator instance.
     *
     * @return void
     */
    public function testItCanCreateStatic(): void
    {
        $this->assertInstanceOf(Validator::class, Validator::make());
    }
}
