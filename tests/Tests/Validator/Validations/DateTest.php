<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests validation of date fields with custom formats.
 * Checks that validation correctly identifies valid and invalid dates,
 * and that the invert (not) mode works properly.
 */
#[CoversClass(Validator::class)]
class DateTest extends TestCase
{
    /**
     * Test it can render date validation.
     *
     * @return void
     */
    public function testItCanRenderDateValidation(): void
    {
        $this->assertEquals('date,d/m/Y', vr()->date('d/m/Y'));
    }

    /**
     * Test it can render invert date validation.
     *
     * @return void
     */
    public function testItCanRenderInvertDateValidation(): void
    {
        $this->assertEquals('invert_date,d/m/Y', vr()->not()->date('d/m/Y'));
    }

    /**
     * Test it can validate date with correct input.
     *
     * @return void
     */
    public function testItCanValidateDateWithCorrectInput(): void
    {
        $correct = [
            'test1' => '2022/11/01',
            'test2' => '31-12-2019 10:10',
        ];

        $val = new Validator($correct);

        $val->test1->date('Y/m/d');
        $val->test2->date('d-m-Y H:i');

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can validate date (not) with correct input.
     *
     * @return void
     */
    public function testItCanValidateDateNotWithCorrectInput(): void
    {
        $correct = [
            'test1' => '2022/11/01',
            'test2' => '31-12-2019 10:10',
        ];

        $val = new Validator($correct);

        $val->test1->not->date('Y/m/d');
        $val->test2->not->date('d-m-Y H:i');

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate date with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateDateWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => '2022/12/32',
            'test2' => '31-12-2019 10:70',
        ];

        $val = new Validator($incorrect);

        $val->test1->date('Y/m/d');
        $val->test2->date('d-m-Y H:i');

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate date (not) with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateDateNotWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => '2022/12/32',
            'test2' => '31-12-2019 10:70',
        ];

        $val = new Validator($incorrect);

        $val->test1->not->date('Y/m/d');
        $val->test2->not->date('d-m-Y H:i');

        $this->assertTrue($val->isValid());
    }
}
