<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the float validation rule.
 */
#[CoversClass(Validator::class)]
class ValidFloatTest extends TestCase
{
    /**
     * Test that float validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderFloatValidation(): void
    {
        $this->assertSame('float', (string) vr()->float());
    }

    /**
     * Test that inverted float validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertFloatValidation(): void
    {
        $this->assertSame('invert_float', (string) vr()->not()->float());
    }

    /**
     * Test float validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateFloatWithCorrectInput(): void
    {
        $correct = [
            'test1' => 0,
            'test2' => 1.1,
            'test3' => '1.1',
            'test4' => -1.1,
            'test5' => '-1.1',
        ];

        $val = new Validator($correct);

        $field_names = array_keys($correct);
        $val->field(...$field_names)->float();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test float (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateFloatNotWithCorrectInput(): void
    {
        $correct = [
            'test1' => 0,
            'test2' => 1.1,
            'test3' => '1.1',
            'test4' => -1.1,
            'test5' => '-1.1',
        ];

        $val = new Validator($correct);

        $field_names = array_keys($correct);
        $val->field(...$field_names)->not->float();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test float validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateFloatWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => '1,1',
            'test2' => '1.0,0',
            'test3' => '1,0.0',
            'test4' => 'text',
        ];

        $val = new Validator($incorrect);

        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->float();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test float (not) validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateFloatNotWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => '1,1',
            'test2' => '1.0,0',
            'test3' => '1,0.0',
            'test4' => 'text',
        ];

        $val = new Validator($incorrect);

        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->not->float();

        $this->assertTrue($val->isValid());
    }
}
