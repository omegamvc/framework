<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the integer validation rule.
 */
#[CoversClass(Validator::class)]
class IntegerTest extends TestCase
{
    /**
     * Test that integer validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderIntegerValidation(): void
    {
        $rule = vr()->integer();
        $this->assertSame('integer', (string)$rule);
    }

    /**
     * Test that inverted integer validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertIntegerValidation(): void
    {
        $rule = vr()->not->integer();
        $this->assertSame('invert_integer', (string)$rule);
    }

    /**
     * Test integer validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateIntegerWithCorrectInput(): void
    {
        $correct = [
            'test1' => '123',
            'test2' => 123,
            'test3' => -1,
            'test4' => 0,
            'test5' => '0',
        ];

        $val = new Validator($correct);
        $val->field(...array_keys($correct))->integer();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test inverted integer validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateInvertIntegerWithCorrectInput(): void
    {
        $correct = [
            'test1' => '123',
            'test2' => 123,
            'test3' => -1,
            'test4' => 0,
            'test5' => '0',
        ];

        $val = new Validator($correct);
        $val->field(...array_keys($correct))->not->integer();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test integer validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateIntegerWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => 'text',
            'test2' => true,
            'test3' => null,
            'test4' => 1.1,
            'test5' => '1.1',
            'test6' => ['array'],
        ];

        $val = new Validator($incorrect);
        $val->field(...array_keys($incorrect))->integer();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test inverted integer validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateInvertIntegerWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => 'text',
            'test2' => true,
            'test3' => null,
            'test4' => 1.1,
            'test5' => '1.1',
            'test6' => ['array'],
        ];

        $val = new Validator($incorrect);
        $val->field(...array_keys($incorrect))->not->integer();

        $this->assertTrue($val->isValid());
    }
}
