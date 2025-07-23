<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the max_numeric validation rule.
 */
#[CoversClass(Validator::class)]
class MaxNumericTest extends TestCase
{
    /**
     * Test that max_numeric validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderMaxNumericValidation(): void
    {
        $rule = vr()->max_numeric(50);
        $this->assertSame('max_numeric,50', (string) $rule);
    }

    /**
     * Test that inverted max_numeric validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertMaxNumericValidation(): void
    {
        $rule = vr()->not->max_numeric(50);
        $this->assertSame('invert_max_numeric,50', (string) $rule);
    }

    /**
     * Test max_numeric validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateMaxNumericWithCorrectInput(): void
    {
        $correct = [
            'test1' => 2,
            'test2' => 1,
            'test3' => '',
        ];

        $val = new Validator($correct);
        $val->field('test1', 'test2', 'test3')->max_numeric(2);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test inverted max_numeric validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateInvertMaxNumericWithCorrectInput(): void
    {
        $correct = [
            'test1' => 2,
            'test2' => 1,
            'test3' => '',
        ];

        $val = new Validator($correct);
        $val->field('test1', 'test2', 'test3')->not->max_numeric(2);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test max_numeric validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateMaxNumericWithIncorrectInput(): void
    {
        $incorrect = [
            'test' => 3,
        ];

        $val = new Validator($incorrect);
        $val->test->max_numeric(2);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test inverted max_numeric validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateInvertMaxNumericWithIncorrectInput(): void
    {
        $incorrect = [
            'test' => 3,
        ];

        $val = new Validator($incorrect);
        $val->test->not->max_numeric(2);

        $this->assertTrue($val->isValid());
    }
}
