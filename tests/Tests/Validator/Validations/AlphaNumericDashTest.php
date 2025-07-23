<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests the alpha_numeric_dash validation rule rendering and its validation behavior with valid and invalid inputs.
 */
#[CoversClass(Validator::class)]
class AlphaNumericDashTest extends TestCase
{
    /**
     * Test it can render alpha_numeric_dash validation rule.
     *
     * @return void
     */
    public function testItCanRenderAlphaNumericDashValidation(): void
    {
        $this->assertEquals('alpha_numeric_dash', (string) vr()->alpha_numeric_dash());
    }

    /**
     * Test it can render invert alpha_numeric_dash validation rule.
     *
     * @return void
     */
    public function testItCanRenderInvertAlphaNumericDashValidation(): void
    {
        $this->assertEquals('invert_alpha_numeric_dash', (string) vr()->not()->alpha_numeric_dash());
    }

    /**
     * Test it can validate alpha_numeric_dash with correct input.
     *
     * @return void
     */
    public function testItCanValidateAlphaNumericDashWithCorrectInput(): void
    {
        $correct = ['test' => 'azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ123-_'];
        $val = new Validator($correct);
        $val->test->alpha_numeric_dash();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can validate invert alpha_numeric_dash with correct input.
     *
     * @return void
     */
    public function testItCanValidateInvertAlphaNumericDashWithCorrectInput(): void
    {
        $correct = ['test' => 'azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ123-_'];
        $val = new Validator($correct);
        $val->test->not()->alpha_numeric_dash();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate alpha_numeric_dash with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateAlphaNumericDashWithIncorrectInput(): void
    {
        $incorrect = ['test' => 'azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ123-_ '];
        $val = new Validator($incorrect);
        $val->test->alpha_numeric_dash();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate invert alpha_numeric_dash with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateInvertAlphaNumericDashWithIncorrectInput(): void
    {
        $incorrect = ['test' => 'azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ123-_ '];
        $val = new Validator($incorrect);
        $val->test->not()->alpha_numeric_dash();

        $this->assertTrue($val->isValid());
    }
}
