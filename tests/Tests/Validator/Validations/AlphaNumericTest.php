<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests the alpha_numeric validation rule rendering and its validation behavior with valid and invalid inputs.
 */
#[CoversClass(Validator::class)]
class AlphaNumericTest extends TestCase
{
    /**
     * Test it can render alpha_numeric validation rule.
     *
     * @return void
     */
    public function testItCanRenderAlphaNumericValidation(): void
    {
        $this->assertEquals('alpha_numeric', (string) vr()->alpha_numeric());
    }

    /**
     * Test it can render invert alpha_numeric validation rule.
     *
     * @return void
     */
    public function testItCanRenderInvertAlphaNumericValidation(): void
    {
        $this->assertEquals('invert_alpha_numeric', (string) vr()->not()->alpha_numeric());
    }

    /**
     * Test it can validate alpha_numeric with correct input.
     *
     * @return void
     */
    public function testItCanValidateAlphaNumericWithCorrectInput(): void
    {
        $correct = ['test' => '123azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ'];
        $val = new Validator($correct);
        $val->test->alpha_numeric();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can validate invert alpha_numeric with correct input.
     *
     * @return void
     */
    public function testItCanValidateInvertAlphaNumericWithCorrectInput(): void
    {
        $correct = ['test' => '123azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ'];
        $val = new Validator($correct);
        $val->test->not()->alpha_numeric();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate alpha_numeric with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateAlphaNumericWithIncorrectInput(): void
    {
        $incorrect = ['test' => 'hello *(^*^*&\')'];
        $val = new Validator($incorrect);
        $val->test->alpha_numeric();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate invert alpha_numeric with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateInvertAlphaNumericWithIncorrectInput(): void
    {
        $incorrect = ['test' => 'hello *(^*^*&\')'];
        $val = new Validator($incorrect);
        $val->test->not()->alpha_numeric();

        $this->assertTrue($val->isValid());
    }
}
