<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the required validation.
 */
#[CoversClass(Validator::class)]
class RequiredTest extends TestCase
{
    /**
     * Test that required validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderRequiredValidation(): void
    {
        $this->assertSame('required', (string) vr()->required());
    }

    /**
     * Test that inverted required validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertRequiredValidation(): void
    {
        $this->assertSame('invert_required', (string) vr()->not()->required());
    }

    /**
     * Test required validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateRequiredWithCorrectInput(): void
    {
        $correct = [
            'test1' => 'test',
            'test2' => '0',
            'test3' => 0.0,
            'test4' => 0,
            'test5' => false,
        ];

        $val = new Validator($correct);
        $val->field(...array_keys($correct))->required();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test required (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateRequiredNotWithCorrectInput(): void
    {
        $correct = [
            'test1' => 'test',
            'test2' => '0',
            'test3' => 0.0,
            'test4' => 0,
            'test5' => false,
        ];

        $val = new Validator($correct);
        $val->field(...array_keys($correct))->not->required();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test required validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateRequiredWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => null,
            'test2' => '',
        ];

        $val = new Validator($incorrect);
        $val->field(...array_keys($incorrect))->required();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test required (not) validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateRequiredNotWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => null,
            'test2' => '',
        ];

        $val = new Validator($incorrect);
        $val->field(...array_keys($incorrect))->not->required();

        $this->assertTrue($val->isValid());
    }
}
