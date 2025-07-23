<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the IBAN validation rule.
 */
#[CoversClass(Validator::class)]
class IbanTest extends TestCase
{
    /**
     * Test that IBAN validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderIbanValidation(): void
    {
        $rule = vr()->iban();
        $this->assertSame('iban', (string)$rule);
    }

    /**
     * Test that inverted IBAN validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertIbanValidation(): void
    {
        $rule = vr()->not->iban();
        $this->assertSame('invert_iban', (string)$rule);
    }

    /**
     * Test IBAN validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateIbanWithCorrectInput(): void
    {
        $correct = [
            'test1' => 'FR7630006000011234567890189',
            'test2' => 'ES7921000813610123456789',
        ];

        $val = new Validator($correct);
        $val->field(...array_keys($correct))->iban();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test inverted IBAN validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateInvertIbanWithCorrectInput(): void
    {
        $correct = [
            'test1' => 'FR7630006000011234567890189',
            'test2' => 'ES7921000813610123456789',
        ];

        $val = new Validator($correct);
        $val->field(...array_keys($correct))->not->iban();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test IBAN validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateIbanWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => 'FR7630006000011234567890181',
            'test2' => 'E7921000813610123456789',
            'test3' => 'text',
        ];

        $val = new Validator($incorrect);
        $val->field(...array_keys($incorrect))->iban();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test inverted IBAN validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateInvertIbanWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => 'FR7630006000011234567890181',
            'test2' => 'E7921000813610123456789',
            'test3' => 'text',
        ];

        $val = new Validator($incorrect);
        $val->field(...array_keys($incorrect))->not->iban();

        $this->assertTrue($val->isValid());
    }
}
