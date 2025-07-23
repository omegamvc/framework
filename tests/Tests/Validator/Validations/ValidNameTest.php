<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the valid_name validation rule.
 */
#[CoversClass(Validator::class)]
class ValidNameTest extends TestCase
{
    /**
     * Test that valid_name validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderValidNameValidation(): void
    {
        $this->assertSame('valid_name', (string) vr()->valid_name());
    }

    /**
     * Test that inverted valid_name validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertValidNameValidation(): void
    {
        $this->assertSame('invert_valid_name', (string) vr()->not()->valid_name());
    }

    /**
     * Test valid_name validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateValidNameWithCorrectInput(): void
    {
        $correct = [
            'test1' => 'taylorotwell',
            'tets2' => '',
        ];

        $val = new Validator($correct);

        $field_names = array_keys($correct);
        $val->field(...$field_names)->valid_name();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test valid_name (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateValidNameNotWithCorrectInput(): void
    {
        $correct = [
            'test1' => 'taylorotwell',
            'tets2' => '',
        ];

        $val = new Validator($correct);

        $field_names = array_keys($correct);
        $val->field(...$field_names)->not->valid_name();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_name validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateValidNameWithIncorrectInput(): void
    {
        $incorrect = [
            'tets1' => 'sa`ad',
            'tets2' => 's@ad',
            'test3' => 'Mr. Sigurd Heller MD',
        ];

        $val = new Validator($incorrect);

        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->valid_name();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_name (not) validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateValidNameNotWithIncorrectInput(): void
    {
        $incorrect = [
            'tets1' => 'sa`ad',
            'tets2' => 's@ad',
            'test3' => 'Mr. Sigurd Heller MD',
        ];

        $val = new Validator($incorrect);

        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->not->valid_name();

        $this->assertTrue($val->isValid());
    }
}
