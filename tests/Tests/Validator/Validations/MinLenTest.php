<?php

declare(strict_types=1);

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Test the min_len validation rule.
 */
#[CoversClass(Validator::class)]
final class MinLenTest extends TestCase
{
    /**
     * Test that min_len validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderMinLenValidation(): void
    {
        $this->assertSame('min_len,240', (string) vr()->min_len(240));
    }

    /**
     * Test that inverted min_len validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertMinLenValidation(): void
    {
        $this->assertSame('invert_min_len,240', (string) vr()->not()->min_len(240));
    }

    /**
     * Test that min_len validation passes with correct input.
     *
     * @return void
     */
    public function testItCanValidateMinLenWithCorrectInput(): void
    {
        $val = new Validator([
            'test1' => 'ñándú',
            'test2' => 'ñán',
            'test3' => '',
        ]);

        $val->test1->min_len(5);
        $val->test2->min_len(2);
        $val->test3->min_len(2);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test that inverted min_len validation fails with correct input.
     *
     * @return void
     */
    public function testItCanValidateMinLenNotWithCorrectInput(): void
    {
        $val = new Validator([
            'test1' => 'ñándú',
            'test2' => 'ñán',
            'test3' => '',
        ]);

        $val->test1->not->min_len(5);
        $val->test2->not->min_len(2);
        $val->test3->not->min_len(2);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test that min_len validation fails with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateMinLenWithIncorrectInput(): void
    {
        $val = new Validator(['test' => 'ñ']);
        $val->test->min_len(2);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test that inverted min_len validation passes with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateMinLenNotWithIncorrectInput(): void
    {
        $val = new Validator(['test' => 'ñ']);
        $val->test->not()->min_len(2);

        $this->assertTrue($val->isValid());
    }
}
