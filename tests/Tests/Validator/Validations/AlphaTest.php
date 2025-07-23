<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests the alpha validation rule rendering and its validation behavior with valid and invalid inputs.
 */
#[CoversClass(Validator::class)]
class AlphaTest extends TestCase
{
    /**
     * Test it can render alpha validation rule.
     *
     * @return void
     */
    public function testItCanRenderAlphaValidation(): void
    {
        $this->assertEquals('alpha', (string) vr()->alpha());
    }

    /**
     * Test it can render invert alpha validation rule.
     *
     * @return void
     */
    public function testItCanRenderInvertAlphaValidation(): void
    {
        $this->assertEquals('invert_alpha', (string) vr()->not()->alpha());
    }

    /**
     * Test it can validate alpha with correct input.
     *
     * @return void
     */
    public function testItCanValidateAlphaWithCorrectInput(): void
    {
        $correct = ['test' => 'azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ'];
        $val = new Validator($correct);
        $val->test->alpha();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can validate invert alpha with correct input.
     *
     * @return void
     */
    public function testItCanValidateInvertAlphaWithCorrectInput(): void
    {
        $correct = ['test' => 'azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ'];
        $val = new Validator($correct);
        $val->test->not()->alpha();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate alpha with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateAlphaWithIncorrectInput(): void
    {
        $incorrect = ['test' => '123azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ'];
        $val = new Validator($incorrect);
        $val->test->alpha();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate invert alpha with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateInvertAlphaWithIncorrectInput(): void
    {
        $incorrect = ['test' => '123azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ'];
        $val = new Validator($incorrect);
        $val->test->not()->alpha();

        $this->assertTrue($val->isValid());
    }
}
