<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the valid_ip validation rule.
 */
#[CoversClass(Validator::class)]
class ValidIpTest extends TestCase
{
    /**
     * Test that valid_ip validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderValidIpValidation(): void
    {
        $this->assertSame('valid_ip', (string) vr()->valid_ip());
    }

    /**
     * Test that inverted valid_ip validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertValidIpValidation(): void
    {
        $this->assertSame('invert_valid_ip', (string) vr()->not()->valid_ip());
    }

    /**
     * Test valid_ip validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateValidIpWithCorrectInput(): void
    {
        $correct = [
            'test1' => '2001:0db8:85a3:08d3:1319:8a2e:0370:7334', // IPv6
            'test2' => '127.0.0.1',                                // IPv4
            'test3' => '255.255.255.255',                          // IPv4 broadcast
        ];

        $val = new Validator($correct);

        $field_names = array_keys($correct);
        $val->field(...$field_names)->valid_ip();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test valid_ip (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateValidIpNotWithCorrectInput(): void
    {
        $correct = [
            'test1' => '2001:0db8:85a3:08d3:1319:8a2e:0370:7334',
            'test2' => '127.0.0.1',
            'test3' => '255.255.255.255',
        ];

        $val = new Validator($correct);

        $field_names = array_keys($correct);
        $val->field(...$field_names)->not->valid_ip();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_ip validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateValidIpWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => '2001:0zb8:85a3:08d3:1319:8a2e:0370:7334', // Invalid hex 'z'
            'test2' => '0,0,0,0',                                  // Invalid IP format
            'test3' => '256.0.0.0',                                // Out of range IPv4
        ];

        $val = new Validator($incorrect);

        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->valid_ip();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_ip (not) validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateValidIpNotWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => '2001:0zb8:85a3:08d3:1319:8a2e:0370:7334',
            'test2' => '0,0,0,0',
            'test3' => '256.0.0.0',
        ];

        $val = new Validator($incorrect);

        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->not->valid_ip();

        $this->assertTrue($val->isValid());
    }
}
