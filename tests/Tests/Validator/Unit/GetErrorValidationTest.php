<?php

declare(strict_types=1);

namespace Tests\Validator\Unit;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for retrieving error messages from Validator.
 */
#[CoversClass(Validator::class)]
class GetErrorValidationTest extends TestCase
{
    public function testCanGetErrorMessageWhenValidationFailsUsingGetError(): void
    {
        $valid = new Validator(['test' => 'test']);
        $valid->field('test')->min_len(5);

        $this->assertCount(1, $valid->getError());
    }

    public function testCanGetErrorMessageWhenValidationFailsUsingIfValid(): void
    {
        $valid = new Validator(['test' => 'test']);
        $valid->field('test')->min_len(5);

        $calledElse = false;
        $valid->if_valid(function () {
            $this->assertTrue(true);
        })->else(function ($err) use (&$calledElse) {
            $calledElse = true;
            $this->assertCount(1, $err);
        });

        $this->assertTrue($calledElse);
    }

    public function testCanGetErrorMessageWhenValidationFailsUsingValidOrError(): void
    {
        $valid = new Validator(['test' => 'test']);
        $valid->field('test')->min_len(5);

        $this->assertCount(1, $valid->validOrError());
    }
}
