<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the valid_ipv4 validation rule.
 */
#[CoversClass(Validator::class)]
class ValidIpv4Test extends TestCase
{
    /**
     * Test that valid_ipv4 validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderValidIpv4Validation(): void
    {
        $this->assertSame('valid_ipv4', (string) vr()->valid_ipv4());
    }

    /**
     * Test that inverted valid_ipv4 validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertValidIpv4Validation(): void
    {
        $this->assertSame('invert_valid_ipv4', (string) vr()->not()->valid_ipv4());
    }

    /**
     * Test valid_ipv4 validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateValidIpv4WithCorrectInput(): void
    {
        $correct = [
            'test1' => '127.0.0.1',
            'test2' => '1.1.1.1',
            'test3' => '255.255.255.255',
        ];

        $val = new Validator($correct);

        $field_names = array_keys($correct);
        $val->field(...$field_names)->valid_ipv4();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test valid_ipv4 (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateValidIpv4NotWithCorrectInput(): void
    {
        $correct = [
            'test1' => '127.0.0.1',
            'test2' => '1.1.1.1',
            'test3' => '255.255.255.255',
        ];

        $val = new Validator($correct);

        $field_names = array_keys($correct);
        $val->field(...$field_names)->not->valid_ipv4();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_ipv4 validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateValidIpv4WithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => '2001:0zb8:85a3:08d3:1319:8a2e:0370:7334', // IPv6 invalid (wrong hex)
            'test2' => '0,0,0,0',                                  // invalid format
            'test3' => '256.0.0.0',                                // invalid IPv4
        ];

        $val = new Validator($incorrect);

        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->valid_ipv4();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_ipv4 (not) validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateValidIpv4NotWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => '2001:0zb8:85a3:08d3:1319:8a2e:0370:7334',
            'test2' => '0,0,0,0',
            'test3' => '256.0.0.0',
        ];

        $val = new Validator($incorrect);

        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->not->valid_ipv4();

        $this->assertTrue($val->isValid());
    }
}
