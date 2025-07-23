<?php

declare(strict_types=1);

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Test the min_age validation rule.
 */
#[CoversClass(Validator::class)]
final class MinAgeTest extends TestCase
{
    /**
     * Test that min_age validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderMinAgeValidation(): void
    {
        $this->assertSame('min_age,18', (string) vr()->min_age(18));
    }

    /**
     * Test that inverted min_age validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertMinAgeValidation(): void
    {
        $this->assertSame('invert_min_age,18', (string) vr()->not()->min_age(18));
    }

    /**
     * Test that min_age validation passes with correct input.
     *
     * @return void
     */
    public function testItCanValidateMinAgeWithCorrectInput(): void
    {
        $val = new Validator(['test' => '1997-06-16']);
        $val->test->min_age(23);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test that inverted min_age validation fails with correct input.
     *
     * @return void
     */
    public function testItCanValidateMinAgeNotWithCorrectInput(): void
    {
        $val = new Validator(['test' => '1997-06-16']);
        $val->test->not()->min_age(23);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test that min_age validation fails with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateMinAgeWithIncorrectInput(): void
    {
        $val = new Validator(['test' => '2022-01-11']);
        $val->test->min_age(23);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test that inverted min_age validation passes with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateMinAgeNotWithIncorrectInput(): void
    {
        $val = new Validator(['test' => '2022-01-11']);
        $val->test->not()->min_age(23);

        $this->assertTrue($val->isValid());
    }
}
