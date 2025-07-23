<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the exact length validation rule.
 */
#[CoversClass(Validator::class)]
class ExactLenTest extends TestCase
{
    /**
     * Test that exact_len validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderExactLenValidation(): void
    {
        $rule = vr()->exact_len(240);
        $this->assertSame('exact_len,240', (string)$rule);
    }

    /**
     * Test that inverted exact_len validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertExactLenValidation(): void
    {
        $rule = vr()->not->exact_len(240);
        $this->assertSame('invert_exact_len,240', (string)$rule);
    }

    /**
     * Test that exact_len validation works with correct input.
     *
     * @return void
     */
    public function testItCanValidateExactLenWithCorrectInput(): void
    {
        $correct = ['test' => 'ñándú'];
        $val = new Validator($correct);
        $val->test->exact_len(5);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test that inverted exact_len validation works with correct input.
     *
     * @return void
     */
    public function testItCanValidateInvertExactLenWithCorrectInput(): void
    {
        $correct = ['test' => 'ñándú'];
        $val = new Validator($correct);
        $val->test->not->exact_len(5);

        $this->assertFalse($val->isValid());
    }
}
