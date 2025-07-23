<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the valid_ipv6 validation rule.
 */
#[CoversClass(Validator::class)]
class ValidIpv6Test extends TestCase
{
    /**
     * Test that valid_ipv6 validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderValidIpv6Validation(): void
    {
        $this->assertSame('valid_ipv6', (string) vr()->valid_ipv6());
    }

    /**
     * Test that inverted valid_ipv6 validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertValidIpv6Validation(): void
    {
        $this->assertSame('invert_valid_ipv6', (string) vr()->not()->valid_ipv6());
    }

    /**
     * Test valid_ipv6 validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateValidIpv6WithCorrectInput(): void
    {
        $correct = [
            'test1' => '2001:0db8:85a3:08d3:1319:8a2e:0370:7334',
        ];

        $val = new Validator($correct);

        $field_names = array_keys($correct);
        $val->field(...$field_names)->valid_ipv6();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test valid_ipv6 (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateValidIpv6NotWithCorrectInput(): void
    {
        $correct = [
            'test1' => '2001:0db8:85a3:08d3:1319:8a2e:0370:7334',
        ];

        $val = new Validator($correct);

        $field_names = array_keys($correct);
        $val->field(...$field_names)->not->valid_ipv6();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_ipv6 validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateValidIpv6WithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => '2001;0db8;85a3;08d3;1319;8a2e;0370;7334', // wrong separator ;
            'test2' => '0,0,0,0',                                  // invalid format
            'test3' => '256.0.0.0',                                // invalid IPv4 in IPv6 test
        ];

        $val = new Validator($incorrect);

        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->valid_ipv6();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test valid_ipv6 (not) validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateValidIpv6NotWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => '2001;0db8;85a3;08d3;1319;8a2e;0370;7334',
            'test2' => '0,0,0,0',
            'test3' => '256.0.0.0',
        ];

        $val = new Validator($incorrect);

        $field_names = array_keys($incorrect);
        $val->field(...$field_names)->not->valid_ipv6();

        $this->assertTrue($val->isValid());
    }
}
