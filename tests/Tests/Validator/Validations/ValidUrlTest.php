<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the valid_url validation rule.
 */
#[CoversClass(Validator::class)]
class ValidUrlTest extends TestCase
{
    /**
     * Test that valid_url validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderValidUrlValidation(): void
    {
        $this->assertSame('valid_url', (string) vr()->valid_url());
    }

    /**
     * Test that inverted valid_url validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertValidUrlValidation(): void
    {
        $this->assertSame('invert_valid_url', (string) vr()->not->valid_url());
    }

    /**
     * Test valid_url validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateValidUrlWithCorrectInput(): void
    {
        $correct = [
            'test1' => 'http://test.com/',
            'test2' => 'http://test.com',
            'test3' => 'https://test.com',
            'test4' => 'tcp://test.com',
            'test5' => 'ftp://test.com',
        ];

        $val = new Validator($correct);

        $field_names = array_keys($correct);
        $val->field(...$field_names)->valid_url();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test valid_url (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateValidUrlNotWithCorrectInput(): void
    {
        $correct = [
            'test1' => 'http://test.com/',
            'test2' => 'http://test.com',
            'test3' => 'https://test.com',
            'test4' => 'tcp://test.com',
            'test5' => 'ftp://test.com',
        ];

        $val = new Validator($correct);

        $field_names = array_keys($correct);
        $val->field(...$field_names)->not->valid_url();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_url validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateValidUrlWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => 'example.com',
            'test2' => 'text',
        ];

        $val = new Validator($incorrect);

        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->valid_url();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_url (not) validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateValidUrlNotWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => 'example.com',
            'test2' => 'text',
        ];

        $val = new Validator($incorrect);

        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->not->valid_url();

        $this->assertTrue($val->isValid());
    }
}
