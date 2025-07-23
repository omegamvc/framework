<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests the boolean validation rule rendering and validation with different input scenarios.
 */
#[CoversClass(Validator::class)]
class BooleanTest extends TestCase
{
    /**
     * Test it can render boolean validation without strict mode.
     *
     * @return void
     */
    public function testItCanRenderBooleanValidation(): void
    {
        $this->assertEquals('boolean', (string) vr()->boolean(false));
    }

    /**
     * Test it can render boolean validation with strict mode.
     *
     * @return void
     */
    public function testItCanRenderBooleanValidationStrict(): void
    {
        $this->assertEquals('boolean,strict', (string) vr()->boolean(true));
    }

    /**
     * Test it can render inverted boolean validation without strict mode.
     *
     * @return void
     */
    public function testItCanRenderInvertBooleanValidation(): void
    {
        $this->assertEquals('invert_boolean', (string) vr()->not()->boolean(false));
    }

    /**
     * Test it can render inverted boolean validation with strict mode.
     *
     * @return void
     */
    public function testItCanRenderInvertBooleanValidationStrict(): void
    {
        $this->assertEquals('invert_boolean,strict', (string) vr()->not()->boolean(true));
    }

    /**
     * Test it can validate boolean with correct input (non-strict).
     *
     * @return void
     */
    public function testItCanValidateBooleanWithCorrectInput(): void
    {
        $correct = [
            'test1' => 'true',
            'test2' => 'false',
            'test3' => 'on',
            'test4' => 'off',
            'test5' => '1',
            'test6' => '0',
            'test7' => 'yes',
            'test8' => 'no',
        ];

        $val = new Validator($correct);
        $fieldNames = array_keys($correct);
        $val->field(...$fieldNames)->boolean(false);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can validate boolean with correct input (strict mode).
     *
     * @return void
     */
    public function testItCanValidateBooleanWithCorrectInputStrict(): void
    {
        $correctStrict = [
            'test1' => true,
            'test2' => false,
        ];

        $val = new Validator($correctStrict);
        $val->test1->boolean(true);
        $val->test2->boolean(true);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can validate inverted boolean with correct input (non-strict).
     *
     * @return void
     */
    public function testItCanValidateBooleanNotWithCorrectInput(): void
    {
        $correct = [
            'test1' => 'true',
            'test2' => 'false',
            'test3' => 'on',
            'test4' => 'off',
            'test5' => '1',
            'test6' => '0',
            'test7' => 'yes',
            'test8' => 'no',
        ];

        $val = new Validator($correct);
        $fieldNames = array_keys($correct);
        $val->field(...$fieldNames)->not()->boolean(false);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate inverted boolean with correct input (strict mode).
     *
     * @return void
     */
    public function testItCanValidateBooleanNotWithCorrectInputStrict(): void
    {
        $correctStrict = [
            'test1' => true,
            'test2' => false,
        ];

        $val = new Validator($correctStrict);
        $val->test1->not()->boolean(true);
        $val->test2->not()->boolean(true);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate boolean with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateBooleanWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => 'randomString',
            'test2' => 111,
            'test3' => 'TRUE',
            'test4' => 'False',
        ];

        $val = new Validator($incorrect);
        $fieldNames = array_keys($incorrect);
        $val->field(...$fieldNames)->boolean(false);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate inverted boolean with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateBooleanNotWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => 'randomString',
            'test2' => 111,
            'test3' => 'TRUE',
            'test4' => 'False',
        ];

        $val = new Validator($incorrect);
        $fieldNames = array_keys($incorrect);
        $val->field(...$fieldNames)->not()->boolean(false);

        $this->assertTrue($val->isValid());
    }
}
