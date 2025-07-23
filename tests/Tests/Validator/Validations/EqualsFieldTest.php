<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;

use function Omega\Validator\vr;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests the equalsfield validation rule to verify
 * that a field equals another specified field,
 * including the negated version of the rule.
 */
#[CoversClass(Validator::class)]
class EqualsFieldTest extends TestCase
{
    /**
     * Test that equalsfield validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderEqualsfieldValidation(): void
    {
        $rule = vr()->equalsfield('other_field_name');

        $this->assertSame('equalsfield,other_field_name', (string)$rule);
    }

    /**
     * Test it can render invert equals field validation.
     *
     * @return void
     */
    public function testItCanRenderInvertEqualsfieldValidation(): void
    {
        $rule = vr()->not->equalsfield('other_field_name');

        $this->assertSame('invert_equalsfield,other_field_name', (string)$rule);
    }

    /**
     * Test validation with correct input where fields are equal.
     *
     * @return void
     */
    public function testItCanValidateEqualsfieldWithCorrectInput(): void
    {
        $correct = ['test' => 'string', 'other' => 'string'];

        $val = new Validator($correct);
        $val->test->equalsfield('other');

        $this->assertTrue($val->isValid());
    }

    /**
     * Test negated validation with correct input where fields are equal.
     *
     * @return void
     */
    public function testItCanValidateEqualsfieldNotWithCorrectInput(): void
    {
        $correct = ['test' => 'string', 'other' => 'string'];

        $val = new Validator($correct);
        $val->test->not->equalsfield('other');

        $this->assertFalse($val->isValid());
    }

    /**
     * Test validation with incorrect input where fields are different.
     *
     * @return void
     */
    public function testItCanValidateEqualsfieldWithIncorrectInput(): void
    {
        $incorrect = ['test' => 'string', 'other' => 'different_string'];

        $val = new Validator($incorrect);
        $val->test->equalsfield('other');

        $this->assertFalse($val->isValid());
    }

    /**
     * Test negated validation with incorrect input where fields are different.
     *
     * @return void
     */
    public function testItCanValidateEqualsfieldNotWithIncorrectInput(): void
    {
        $incorrect = ['test' => 'string', 'other' => 'different_string'];

        $val = new Validator($incorrect);
        $val->test->not->equalsfield('other');

        $this->assertTrue($val->isValid());
    }
}
