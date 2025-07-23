<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests the alpha_space validation rule rendering and validation behavior with correct and incorrect inputs.
 */
#[CoversClass(Validator::class)]
class AlphaSpaceTest extends TestCase
{
    /**
     * Test it can render alpha_space validation rule.
     *
     * @return void
     */
    public function testItCanRenderAlphaSpaceValidation(): void
    {
        $this->assertEquals('alpha_space', (string) vr()->alpha_space());
    }

    /**
     * Test it can render invert alpha_space validation rule.
     *
     * @return void
     */
    public function testItCanRenderInvertAlphaSpaceValidation(): void
    {
        $this->assertEquals('invert_alpha_space', (string) vr()->not()->alpha_space());
    }

    /**
     * Test it can validate alpha_space with correct input.
     *
     * @return void
     */
    public function testItCanValidateAlphaSpaceWithCorrectInput(): void
    {
        $correct = ['test' => ' azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ '];
        $val = new Validator($correct);
        $val->test->alpha_space();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can validate invert alpha_space with correct input.
     *
     * @return void
     */
    public function testItCanValidateInvertAlphaSpaceWithCorrectInput(): void
    {
        $correct = ['test' => ' azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ '];
        $val = new Validator($correct);
        $val->test->not()->alpha_space();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate alpha_space with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateAlphaSpaceWithIncorrectInput(): void
    {
        $incorrect = ['test' => '1 azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ'];
        $val = new Validator($incorrect);
        $val->test->alpha_space();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate invert alpha_space with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateInvertAlphaSpaceWithIncorrectInput(): void
    {
        $incorrect = ['test' => '1 azÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ'];
        $val = new Validator($incorrect);
        $val->test->not()->alpha_space();

        $this->assertTrue($val->isValid());
    }
}
