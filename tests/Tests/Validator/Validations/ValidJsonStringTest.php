<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the valid_json_string validation rule.
 */
#[CoversClass(Validator::class)]
class ValidJsonStringTest extends TestCase
{
    /**
     * Test that valid_json_string validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderValidJsonStringValidation(): void
    {
        $this->assertSame('valid_json_string', (string) vr()->valid_json_string());
    }

    /**
     * Test that inverted valid_json_string validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertValidJsonStringValidation(): void
    {
        $this->assertSame('invert_valid_json_string', (string) vr()->not()->valid_json_string());
    }

    /**
     * Test valid_json_string validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateValidJsonStringWithCorrectInput(): void
    {
        $correct = [
            'test1' => '{}',
            'test2' => '{"testing": true}',
        ];

        $val = new Validator($correct);

        $field_names = array_keys($correct);
        $val->field(...$field_names)->valid_json_string();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test valid_json_string (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateValidJsonStringNotWithCorrectInput(): void
    {
        $correct = [
            'test1' => '{}',
            'test2' => '{"testing": true}',
        ];

        $val = new Validator($correct);

        $field_names = array_keys($correct);
        $val->field(...$field_names)->not->valid_json_string();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_json_string validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateValidJsonStringWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => '{}}',
            'test2' => '{test:true}',
            'test3' => '{"test":text}',
        ];

        $val = new Validator($incorrect);

        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->valid_json_string();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_json_string (not) validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateValidJsonStringNotWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => '{}}',
            'test2' => '{test:true}',
            'test3' => '{"test":text}',
        ];

        $val = new Validator($incorrect);

        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->not->valid_json_string();

        $this->assertTrue($val->isValid());
    }
}
