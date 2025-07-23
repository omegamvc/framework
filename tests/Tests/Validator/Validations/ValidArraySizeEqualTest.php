<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the valid_array_size_equal validation.
 */
#[CoversClass(Validator::class)]
class ValidArraySizeEqualTest extends TestCase
{
    /**
     * Test that valid_array_size_equal validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderValidArraySizeEqualValidation(): void
    {
        $this->assertSame('valid_array_size_equal,1', (string) vr()->valid_array_size_equal(1));
    }

    /**
     * Test that inverted valid_array_size_equal validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertValidArraySizeEqualValidation(): void
    {
        $this->assertSame('invert_valid_array_size_equal,1', (string) vr()->not()->valid_array_size_equal(1));
    }

    /**
     * Test valid_array_size_equal validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateValidArraySizeEqualWithCorrectInput(): void
    {
        $correct = ['test' => [1, 2, 3]];

        $val = new Validator($correct);
        $val->test->valid_array_size_equal(3);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test valid_array_size_equal (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateNotValidArraySizeEqualWithCorrectInput(): void
    {
        $correct = ['test' => [1, 2, 3]];

        $val = new Validator($correct);
        $val->test->not()->valid_array_size_equal(3);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_array_size_equal validation with incorrect input greater than.
     *
     * @return void
     */
    public function testItCanValidateValidArraySizeEqualWithIncorrectInputGreaterThan(): void
    {
        $incorrect = ['test' => [1, 2]];

        $val = new Validator($incorrect);
        $val->test->valid_array_size_equal(3);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_array_size_equal validation with incorrect input less than.
     *
     * @return void
     */
    public function testItCanValidateValidArraySizeEqualWithIncorrectInputLessThan(): void
    {
        $incorrect = ['test' => [1, 2]];

        $val = new Validator($incorrect);
        $val->test->valid_array_size_equal(1);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_array_size_equal (not) validation with incorrect input greater than.
     *
     * @return void
     */
    public function testItCanValidateNotValidArraySizeEqualWithIncorrectInputGreaterThan(): void
    {
        $incorrect = ['test' => [1, 2]];

        $val = new Validator($incorrect);
        $val->test->not->valid_array_size_equal(3);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test valid_array_size_equal (not) validation with incorrect input less than.
     *
     * @return void
     */
    public function testItCanValidateNotValidArraySizeEqualWithIncorrectInputLessThan(): void
    {
        $incorrect = ['test' => [1, 2]];

        $val = new Validator($incorrect);
        $val->test->not->valid_array_size_equal(1);

        $this->assertTrue($val->isValid());
    }
}
