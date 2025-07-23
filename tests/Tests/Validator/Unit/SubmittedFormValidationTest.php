<?php

namespace Tests\Validator\Unit;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validator::class)]
class SubmittedFormValidationTest extends TestCase
{
    protected function tearDown(): void
    {
        // Pulisco $_SERVER dopo ogni test per evitare effetti collaterali
        unset($_SERVER['REQUEST_METHOD']);
    }

    public function testSubmittedFromFormNotMethod(): void
    {
        unset($_SERVER);

        $this->assertFalse(Validator::make()->submitted());
    }

    public function testSubmittedFromFormMethodGet(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->assertFalse(Validator::make()->submitted());
    }

    public function testSubmittedFromFormMethodPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->assertTrue(Validator::make()->submitted());
    }
}
