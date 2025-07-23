<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the regex validation.
 */
#[CoversClass(Validator::class)]
class RegexTest extends TestCase
{
    /**
     * Test that regex validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderRegexValidation(): void
    {
        $this->assertSame('regex,/test-[0-9]{3}/', (string) vr()->regex('/test-[0-9]{3}/'));
    }

    /**
     * Test that inverted regex validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertRegexValidation(): void
    {
        $this->assertSame('invert_regex,/test-[0-9]{3}/', (string) vr()->not()->regex('/test-[0-9]{3}/'));
    }

    /**
     * Test regex validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateRegexWithCorrectInput(): void
    {
        $correct = ['test' => 'validation using gump'];

        $val = new Validator($correct);
        $val->test->regex('/gump/i');

        $this->assertTrue($val->isValid());
    }

    /**
     * Test regex (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateRegexNotWithCorrectInput(): void
    {
        $correct = ['test' => 'validation using gump'];

        $val = new Validator($correct);
        $val->test->not->regex('/gump/i');

        $this->assertFalse($val->isValid());
    }

    /**
     * Test regex validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateRegexWithIncorrectInput(): void
    {
        $incorrect = ['test' => 'testing using pest'];

        $val = new Validator($incorrect);
        $val->test->regex('/gump/i');

        $this->assertFalse($val->isValid());
    }

    /**
     * Test regex (not) validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateRegexNotWithIncorrectInput(): void
    {
        $incorrect = ['test' => 'testing using pest'];

        $val = new Validator($incorrect);
        $val->test->not()->regex('/gump/i');

        $this->assertTrue($val->isValid());
    }
}
