<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests the alpha_numeric_space validation rule rendering and validation behavior with correct and incorrect inputs.
 */
#[CoversClass(Validator::class)]
class AlphaNumericSpaceTest extends TestCase
{
    /**
     * Test it can render alpha_numeric_space validation rule.
     *
     * @return void
     */
    public function testItCanRenderAlphaNumericSpaceValidation(): void
    {
        $this->assertEquals('alpha_numeric_space', (string)vr()->alpha_numeric_space());
    }

    /**
     * Test it can render invert alpha_numeric_space validation rule.
     *
     * @return void
     */
    public function testItCanRenderInvertAlphaNumericSpaceValidation(): void
    {
        $this->assertEquals('invert_alpha_numeric_space', (string)vr()->not()->alpha_numeric_space());
    }

    /**
     * Test it can validate alpha_numeric_space with correct input.
     *
     * @return void
     */
    public function testItCanValidateAlphaNumericSpaceWithCorrectInput(): void
    {
        $correct = ['test' => 'azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ123 '];
        $val = new Validator($correct);
        $val->test->alpha_numeric_space();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can validate invert alpha_numeric_space with correct input.
     *
     * @return void
     */
    public function testItCanValidateInvertAlphaNumericSpaceWithCorrectInput(): void
    {
        $correct = ['test' => 'azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ123 '];
        $val = new Validator($correct);
        $val->test->not()->alpha_numeric_space();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate alpha_numeric_space with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateAlphaNumericSpaceWithIncorrectInput(): void
    {
        $incorrect = ['test' => 'azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ123 -'];
        $val = new Validator($incorrect);
        $val->test->alpha_numeric_space();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate invert alpha_numeric_space with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateInvertAlphaNumericSpaceWithIncorrectInput(): void
    {
        $incorrect = ['test' => 'azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ123 -'];
        $val = new Validator($incorrect);
        $val->test->not()->alpha_numeric_space();

        $this->assertTrue($val->isValid());
    }
}
