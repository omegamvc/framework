<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the street_address validation.
 */
#[CoversClass(Validator::class)]
class StreetAddressTest extends TestCase
{
    /**
     * Test that street_address validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderStreetAddressValidation(): void
    {
        $this->assertSame('street_address', (string) vr()->street_address());
    }

    /**
     * Test that inverted street_address validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertStreetAddressValidation(): void
    {
        $this->assertSame('invert_street_address', (string) vr()->not()->street_address());
    }

    /**
     * Test street_address validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateStreetAddressWithCorrectInput(): void
    {
        $correct = [
            'test'  => '6 Avondans Road',
            'test2' => 'Calle Mediterráneo 2',
        ];

        $val = new Validator($correct);
        $field_names = array_keys($correct);

        $val->field(...$field_names)->street_address();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test street_address (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateNotStreetAddressWithCorrectInput(): void
    {
        $correct = [
            'test'  => '6 Avondans Road',
            'test2' => 'Calle Mediterráneo 2',
        ];

        $val = new Validator($correct);
        $field_names = array_keys($correct);

        $val->field(...$field_names)->not->street_address();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test street_address validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateStreetAddressWithIncorrectInput(): void
    {
        $incorrect = [
            'test'  => 'Avondans Road',
            'test2' => 'text',
        ];

        $val = new Validator($incorrect);
        $field_names = array_keys($incorrect);

        $val->field(...$field_names)->street_address();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test street_address (not) validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateNotStreetAddressWithIncorrectInput(): void
    {
        $incorrect = [
            'test'  => 'Avondans Road',
            'test2' => 'text',
        ];

        $val = new Validator($incorrect);
        $field_names = array_keys($incorrect);

        $val->field(...$field_names)->not->street_address();

        $this->assertTrue($val->isValid());
    }
}
