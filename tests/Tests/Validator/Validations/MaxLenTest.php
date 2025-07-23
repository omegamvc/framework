<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the max_len validation rule.
 */
#[CoversClass(Validator::class)]
class MaxLenTest extends TestCase
{
    /**
     * Test that max_len validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderMaxLenValidation(): void
    {
        $rule = vr()->max_len(240);
        $this->assertSame('max_len,240', (string)$rule);
    }

    /**
     * Test that inverted max_len validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertMaxLenValidation(): void
    {
        $rule = vr()->not->max_len(240);
        $this->assertSame('invert_max_len,240', (string)$rule);
    }

    /**
     * Test max_len validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateMaxLenWithCorrectInput(): void
    {
        $correct = [
            'test1' => 'ñándú',
            'test2' => 'ñ',
            'test3' => '',
        ];

        $val = new Validator($correct);
        $val->test1->max_len(5);
        $val->test2->max_len(2);
        $val->test3->max_len(2);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test inverted max_len validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateInvertMaxLenWithCorrectInput(): void
    {
        $correct = [
            'test1' => 'ñándú',
            'test2' => 'ñ',
            'test3' => '',
        ];

        $val = new Validator($correct);
        $val->test1->not->max_len(5);
        $val->test2->not->max_len(2);
        $val->test3->not->max_len(2);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test max_len validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateMaxLenWithIncorrectInput(): void
    {
        $incorrect = ['test' => 'ñán'];

        $val = new Validator($incorrect);
        $val->test->max_len(2);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test inverted max_len validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateInvertMaxLenWithIncorrectInput(): void
    {
        $incorrect = ['test' => 'ñán'];

        $val = new Validator($incorrect);
        $val->test->not->max_len(2);

        $this->assertTrue($val->isValid());
    }
}
