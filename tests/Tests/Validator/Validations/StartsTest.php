<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the starts validation.
 */
#[CoversClass(Validator::class)]
class StartsTest extends TestCase
{
    /**
     * Test that starts validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderStartsValidation(): void
    {
        $this->assertSame('starts,Z', (string) vr()->starts('Z'));
    }

    /**
     * Test that inverted starts validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertStartsValidation(): void
    {
        $this->assertSame('invert_starts,Z', (string) vr()->not()->starts('Z'));
    }

    /**
     * Test starts validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateStartsWithCorrectInput(): void
    {
        $val = new Validator(['test' => 'test']);
        $val->test->starts('tes');
        $this->assertTrue($val->isValid());
    }

    /**
     * Test starts (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateStartsNotWithCorrectInput(): void
    {
        $val = new Validator(['test' => 'test']);
        $val->test->not->starts('tes');
        $this->assertFalse($val->isValid());
    }

    /**
     * Test starts validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateStartsWithIncorrectInput(): void
    {
        $val = new Validator(['test' => 'ttest']);
        $val->test->starts('tes');
        $this->assertFalse($val->isValid());
    }

    /**
     * Test starts (not) validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateStartsNotWithIncorrectInput(): void
    {
        $val = new Validator(['test' => 'ttest']);
        $val->test->not->starts('tes');
        $this->assertTrue($val->isValid());
    }
}
