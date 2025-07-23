<?php

declare(strict_types=1);

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Test the numeric validation rule.
 */
#[CoversClass(Validator::class)]
final class NumericTest extends TestCase
{
    /**
     * Test that numeric validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderNumericValidation(): void
    {
        $this->assertSame('numeric', (string) vr()->numeric());
    }

    /**
     * Test that inverted numeric validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertNumericValidation(): void
    {
        $this->assertSame('invert_numeric', (string) vr()->not()->numeric());
    }

    /**
     * Test that numeric validation passes with correct input.
     *
     * @return void
     */
    /**
     * Test that numeric validation passes with correct input.
     *
     * @return void
     */
    public function testItCanValidateNumericWithCorrectInput(): void
    {
        $correct = [
            'test1' => 123,
            'test2' => 1.2,
            'test3' => 0,
            'test4' => '0',
            'test5' => -1,
            'test6' => '123',
            'test7' => '-1',
            'test8' => '',
        ];

        $val = new Validator($correct);
        $val->field(...array_keys($correct))->numeric();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test that inverted numeric validation fails with correct input.
     *
     * @return void
     */
    public function testItCanValidateNumericNotWithCorrectInput(): void
    {
        $correct = [
            'test1' => 123,
            'test2' => 1.2,
            'test3' => 0,
            'test4' => '0',
            'test5' => -1,
            'test6' => '123',
            'test7' => '-1',
            'test8' => '',
        ];

        $val = new Validator($correct);
        $val->field(...array_keys($correct))->not->numeric();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test that numeric validation fails with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateNumericWithIncorrectInput(): void
    {
        $val = new Validator(['test' => 'n0t']);
        $val->test->numeric();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test that inverted numeric validation passes with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateNumericNotWithIncorrectInput(): void
    {
        $val = new Validator(['test' => 'n0t']);
        $val->test->not->numeric();

        $this->assertTrue($val->isValid());
    }
}
