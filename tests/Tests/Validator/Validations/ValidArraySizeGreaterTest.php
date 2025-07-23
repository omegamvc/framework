<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the valid_array_size_greater validation.
 */
#[CoversClass(Validator::class)]
class ValidArraySizeGreaterTest extends TestCase
{
    /**
     * Test that valid_array_size_greater validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderValidArraySizeGreaterValidation(): void
    {
        $this->assertSame('valid_array_size_greater,1', (string) vr()->valid_array_size_greater(1));
    }

    /**
     * Test that inverted valid_array_size_greater validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertValidArraySizeGreaterValidation(): void
    {
        $this->assertSame('invert_valid_array_size_greater,1', (string) vr()->not()->valid_array_size_greater(1));
    }

    /**
     * Test valid_array_size_greater validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateValidArraySizeGreaterWithCorrectInput(): void
    {
        $correct = [
            'test1' => [1, 2, 3],
            'test2' => '',
        ];

        $val = new Validator($correct);

        $val->test1->valid_array_size_greater(2);
        $val->test1->valid_array_size_greater(3);
        $val->test2->valid_array_size_greater(3);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test valid_array_size_greater (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateNotValidArraySizeGreaterWithCorrectInput(): void
    {
        $correct = [
            'test1' => [1, 2, 3],
            'test2' => '',
        ];

        $val = new Validator($correct);

        $val->test1->not->valid_array_size_greater(2);
        $val->test1->not->valid_array_size_greater(3);
        $val->test2->not->valid_array_size_greater(3);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_array_size_greater validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateValidArraySizeGreaterWithIncorrectInput(): void
    {
        $incorrect = ['test' => [1, 2]];

        $val = new Validator($incorrect);

        $val->test->valid_array_size_greater(3);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_array_size_greater (not) validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateNotValidArraySizeGreaterWithIncorrectInput(): void
    {
        $incorrect = ['test' => [1, 2]];

        $val = new Validator($incorrect);

        $val->test->not->valid_array_size_greater(3);

        $this->assertTrue($val->isValid());
    }
}
