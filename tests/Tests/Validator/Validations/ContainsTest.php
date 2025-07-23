<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests the contains validation rule rendering and validation with different input scenarios.
 */
#[CoversClass(Validator::class)]
class ContainsTest extends TestCase
{
    /**
     * Test it can render contains validation.
     *
     * @return void
     */
    public function testItCanRenderContainsValidation(): void
    {
        $this->assertEquals('contains,one;two', (string) vr()->contains('one', 'two'));
    }

    /**
     * Test it can render invert contains validation.
     *
     * @return void
     */
    public function testItCanRenderInvertContainsValidation(): void
    {
        $this->assertEquals('invert_contains,one;two', (string) vr()->not()->contains('one', 'two'));
    }

    /**
     * Test it can validate contains with correct input.
     *
     * @return void
     */
    public function testItCanValidateContainsWithCorrectInput(): void
    {
        $correct = [
            'test1' => 'one',
            'test2' => 'with space',
        ];

        $val = new Validator($correct);

        $val->test1->contains('one', 'two');
        $val->test2->contains('one', 'two', 'with space');

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can validate contains (not) with correct input.
     *
     * @return void
     */
    public function testItCanValidateContainsNotWithCorrectInput(): void
    {
        $correct = [
            'test1' => 'one',
            'test2' => 'with space',
        ];

        $val = new Validator($correct);

        $val->test1->not()->contains('one', 'two');
        $val->test2->not()->contains('one', 'two', 'with space');

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate contains with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateContainsWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => 'two',
            'test2' => 'with space',
        ];

        $val = new Validator($incorrect);

        $val->test1->contains('one');
        $val->test2->contains('one', 'with space');

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can validate contains (not) with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateContainsNotWithIncorrectInput(): void
    {
        $incorrect = [
            'test1' => 'two',
            'test2' => 'with space',
        ];

        $val = new Validator($incorrect);

        $val->test1->not()->contains('one');
        $val->test2->not()->contains('one', 'with spec');

        $this->assertTrue($val->isValid());
    }
}
