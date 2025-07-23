<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the valid_array_size_lesser validation.
 */
#[CoversClass(Validator::class)]
class ValidArraySizeLesserTest extends TestCase
{
    /**
     * Test that valid_array_size_lesser validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderValidArraySizeLesserValidation(): void
    {
        $this->assertSame('valid_array_size_lesser,1', (string) vr()->valid_array_size_lesser(1));
    }

    /**
     * Test that inverted valid_array_size_lesser validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertValidArraySizeLesserValidation(): void
    {
        $this->assertSame('invert_valid_array_size_lesser,1', (string) vr()->not()->valid_array_size_lesser(1));
    }

    /**
     * Test valid_array_size_lesser validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateValidArraySizeLesserWithCorrectInput(): void
    {
        $correct = [
            'test1' => [1, 2, 3],
            'test2' => '',
        ];

        $val = new Validator($correct);

        $val->test1->valid_array_size_lesser(3);
        $val->test1->valid_array_size_lesser(4);
        $val->test2->valid_array_size_lesser(3);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test valid_array_size_lesser (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateNotValidArraySizeLesserWithCorrectInput(): void
    {
        $correct = [
            'test1' => [1, 2, 3],
            'test2' => '',
        ];

        $val = new Validator($correct);

        $val->test1->not->valid_array_size_lesser(3);
        $val->test1->not->valid_array_size_lesser(4);
        $val->test2->not->valid_array_size_lesser(3);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_array_size_lesser validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateValidArraySizeLesserWithIncorrectInput(): void
    {
        $incorrect = ['test' => [1, 2]];

        $val = new Validator($incorrect);

        $val->test->valid_array_size_lesser(1);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_array_size_lesser (not) validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateNotValidArraySizeLesserWithIncorrectInput(): void
    {
        $incorrect = ['test' => [1, 2]];

        $val = new Validator($incorrect);

        $val->test->not->valid_array_size_lesser(1);

        $this->assertTrue($val->isValid());
    }
}
