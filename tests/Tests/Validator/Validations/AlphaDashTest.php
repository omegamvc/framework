<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests the alpha_dash validation rule rendering and its validation behavior with valid and invalid inputs.
 */
#[CoversClass(Validator::class)]
class AlphaDashTest extends TestCase
{
    /**
     * Test it can render alpha_dash validation rule.
     *
     * @return void
     */
    public function testItCanRenderAlphaDashValidation(): void
    {
        $this->assertEquals('alpha_dash', (string) vr()->alpha_dash());
    }

    /**
     * Test it can render invert alpha_dash validation rule.
     *
     * @return void
     */
    public function testItCanRenderInvertAlphaDashValidation(): void
    {
        $this->assertEquals('invert_alpha_dash', (string) vr()->not()->alpha_dash());
    }

    /**
     * Test it can validate alpha_dash with correct input.
     *
     * @return void
     */
    public function testItCanValidateAlphaDashWithCorrectInput(): void
    {
        $correct = ['test' => 'my_username-with_dash'];
        $val = new Validator($correct);
        $val->test->alpha_dash();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can validate invert alpha_dash with correct input.
     *
     * @return void
     */
    public function testItCanValidateInvertAlphaDashWithCorrectInput(): void
    {
        $correct = ['test' => 'my_username-with_dash'];
        $val = new Validator($correct);
        $val->test->not()->alpha_dash();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate alpha_dash with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateAlphaDashWithIncorrectInput(): void
    {
        $incorrect = ['test' => 'hello123'];
        $val = new Validator($incorrect);
        $val->test->alpha_dash();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate invert alpha_dash with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateInvertAlphaDashWithIncorrectInput(): void
    {
        $incorrect = ['test' => 'hello123'];
        $val = new Validator($incorrect);
        $val->test->not()->alpha_dash();

        $this->assertTrue($val->isValid());
    }
}
