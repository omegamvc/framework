<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

#[CoversClass(Validator::class)]
/**
 * Tests for the phone_number validation rule.
 */
class PhoneNumberTest extends TestCase
{
    /**
     * Test that phone_number validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderPhoneNumberValidation(): void
    {
        $this->assertSame('phone_number', (string) vr()->phone_number());
    }

    /**
     * Test it can render invert phone number validation.
     *
     * @return void
     */
    public function testItCanRenderInvertPhoneNumberValidation(): void
    {
        $this->assertSame('invert_phone_number', (string) vr()->not()->phone_number());
    }

    /**
     * Test that phone_number validation passes with correct input.
     *
     * @return void
     */
    public function testItCanValidatePhoneNumberWithCorrectInput(): void
    {
        $correct = [
            'test'  => '555-555-5555',
            'test2' => '5555425555',
            'test3' => '555 555 5555',
            'test4' => '1(222) 555-4444',
            'test5' => '1 (519) 555-4422',
            'test6' => '1-555-555-5555',
            'test7' => '1-(555)-555-5555',
        ];

        $val = new Validator($correct);
        $field_names = array_keys($correct);
        $val->field(...$field_names)->phone_number();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test that inverted phone_number validation fails with correct input.
     *
     * @return void
     */
    public function testItCanValidatePhoneNumberNotWithCorrectInput(): void
    {
        $correct = [
            'test'  => '555-555-5555',
            'test2' => '5555425555',
            'test3' => '555 555 5555',
            'test4' => '1(222) 555-4444',
            'test5' => '1 (519) 555-4422',
            'test6' => '1-555-555-5555',
            'test7' => '1-(555)-555-5555',
        ];

        $val = new Validator($correct);
        $field_names = array_keys($correct);
        $val->field(...$field_names)->not->phone_number();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test that phone_number validation fails with incorrect input.
     *
     * @return void
     */
    public function testItCanValidatePhoneNumberWithIncorrectInput(): void
    {
        $incorrect = [
            'test' => '666111222',
            'test2' => '004461234123',
        ];

        $val = new Validator($incorrect);
        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->phone_number();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test that inverted phone_number validation passes with incorrect input.
     *
     * @return void
     */
    public function testItCanValidatePhoneNumberNotWithIncorrectInput(): void
    {
        $incorrect = [
            'test' => '666111222',
            'test2' => '004461234123',
        ];

        $val = new Validator($incorrect);
        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->not->phone_number();

        $this->assertTrue($val->isValid());
    }
}
