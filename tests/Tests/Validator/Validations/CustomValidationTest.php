<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests the addition and behavior of custom validation rules including custom messages.
 */
#[CoversClass(Validator::class)]
class CustomValidationTest extends TestCase
{
    /**
     * Test it can add custom validation.
     *
     * @return void
     */
    public function testItCanAddCustomValidation(): void
    {
        $validation = new Validator(['test' => 2]);

        $validation->field('test')->valid(
            fn ($field, $input, $param, $value) => true,
            'This field is not odd number'
        );

        $this->assertTrue($validation->isValid());
    }

    /**
     * Test it can add custom validation (not).
     *
     * @return void
     */
    public function testItCanAddCustomValidationNot(): void
    {
        $validation = new Validator(['test' => 2]);

        $validation->field('test')->not->valid(
            fn ($field, $input, $param, $value) => true,
            'This field is not odd number'
        );

        $this->assertFalse($validation->isValid());
    }

    /**
     * Test it can add custom validation combined with other rules.
     *
     * @return void
     */
    public function testItCanAddCustomValidationCombineWithOther(): void
    {
        $validation = new Validator(['test' => 22]);

        $validation->field('test')->valid(
            fn ($field, $input, $param, $value) => true,
            'This field is not odd number'
        )->min_len(2);

        $this->assertTrue($validation->isValid());
    }

    /**
     * Test it can add custom validation (not) combined with other rules.
     *
     * @return void
     */
    public function testItCanAddCustomValidationNotCombineWithOther(): void
    {
        $validation = new Validator(['test' => 2]);

        $validation->field('test')->not->valid(
            fn ($field, $input, $param, $value) => true,
            'This field is not odd number'
        )->min_len(2);

        $this->assertFalse($validation->isValid());
    }

    /**
     * Test it can add custom message validation.
     *
     * @return void
     */
    public function testItCanAddCustomMessageValidation(): void
    {
        $validation = new Validator(['test' => 2]);

        $validation->field('test')->valid(
            fn ($field, $input, $param, $value) => false,
            'custom error - {field}'
        );

        $this->assertSame([
            'test' => 'custom error - Test',
        ], $validation->errors->all());
    }

    /**
     * Test it can add custom message validation (not).
     *
     * @return void
     */
    public function testItCanAddCustomMessageValidationNot(): void
    {
        $validation = new Validator(['test' => 2]);

        $validation->field('test')->not->valid(
            fn ($field, $input, $param, $value) => true,
            'custom error - {field}'
        );

        $this->assertFalse($validation->isValid());
        $this->assertSame([
            'test' => 'Not, custom error - Test',
        ], $validation->errors->all());
    }
}
