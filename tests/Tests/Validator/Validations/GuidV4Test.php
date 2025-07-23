<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the GUID v4 validation rule.
 */
#[CoversClass(Validator::class)]
class GuidV4Test extends TestCase
{
    /**
     * Test that guidv4 validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderGuidv4Validation(): void
    {
        $rule = vr()->guidv4();
        $this->assertSame('guidv4', (string)$rule);
    }

    /**
     * Test that inverted guidv4 validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertGuidv4Validation(): void
    {
        $rule = vr()->not->guidv4();
        $this->assertSame('invert_guidv4', (string)$rule);
    }

    /**
     * Test guidv4 validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateGuidv4WithCorrectInput(): void
    {
        $correct = [
            'test1' => 'A98C5A1E-A742-4808-96FA-6F409E799937',
            'test2' => '7deca41a-3479-4f18-9771-3531f742061b',
        ];

        $val = new Validator($correct);
        $val->test1->guidv4();
        $val->test2->guidv4();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test inverted guidv4 validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateInvertGuidv4WithCorrectInput(): void
    {
        $correct = [
            'test1' => 'A98C5A1E-A742-4808-96FA-6F409E799937',
            'test2' => '7deca41a-3479-4f18-9771-3531f742061b',
        ];

        $val = new Validator($correct);
        $val->test1->not->guidv4();
        $val->test2->not->guidv4();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test guidv4 validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateGuidv4WithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => 'A98C5A1EA742480896FA6F409E799937',
            'test2' => '7deca41a-9771-3531f742061b',
        ];

        $val = new Validator($incorrect);
        $val->test1->guidv4();
        $val->test2->guidv4();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test inverted guidv4 validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateInvertGuidv4WithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => 'A98C5A1EA742480896FA6F409E799937',
            'test2' => '7deca41a-9771-3531f742061b',
        ];

        $val = new Validator($incorrect);
        $val->test1->not->guidv4();
        $val->test2->not->guidv4();

        $this->assertTrue($val->isValid());
    }
}
