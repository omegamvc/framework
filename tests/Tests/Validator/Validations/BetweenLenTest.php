<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests the between_len validation rule rendering and validation with different input cases.
 */
#[CoversClass(Validator::class)]
class BetweenLenTest extends TestCase
{
    /**
     * Test it can render between_len validation.
     *
     * @return void
     */
    public function testItCanRenderBetweenLenValidation(): void
    {
        $this->assertEquals('between_len,3;11', (string) vr()->between_len(3, 11));
    }

    /**
     * Test it can render invert between_len validation.
     *
     * @return void
     */
    public function testItCanRenderInvertBetweenLenValidation(): void
    {
        $this->assertEquals('invert_between_len,3;11', (string) vr()->not()->between_len(3, 11));
    }

    /**
     * Test it can validate between_len with correct input.
     *
     * @return void
     */
    public function testItCanValidateBetweenLenWithCorrectInput(): void
    {
        $correct = ['test' => '123'];
        $val = new Validator($correct);
        $val->test->between_len(2, 5);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can validate invert between_len with correct input.
     *
     * @return void
     */
    public function testItCanValidateInvertBetweenLenWithCorrectInput(): void
    {
        $correct = ['test' => '123'];
        $val = new Validator($correct);
        $val->test->not()->between_len(2, 5);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate between_len with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateBetweenLenWithIncorrectInput(): void
    {
        $incorrect = ['test1' => '1', 'test2' => '123456'];
        $val = new Validator($incorrect);
        // Check fields test1 and test2
        $val->field('test1', 'test2')->between_len(2, 5);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate invert between_len with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateInvertBetweenLenWithIncorrectInput(): void
    {
        $incorrect = ['test1' => '1', 'test2' => '123456'];
        $val = new Validator($incorrect);
        // Check fields test1 and test2 with invert
        $val->field('test1', 'test2')->not()->between_len(2, 5);

        $this->assertTrue($val->isValid());
    }
}
