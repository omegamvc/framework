<?php

declare(strict_types=1);

namespace Tests\Validator\Unit;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests to ensure empty validation rules do not cause runtime errors.
 */
#[CoversClass(Validator::class)]
class EmptyRuleValidationTest extends TestCase
{
    /**
     * Test that empty rule does not cause runtime error.
     *
     * @return void
     */
    public function testItEmptyRuleNotMakeRuntimeError(): void
    {
        $valid = new Validator(['test' => 'test']);
        // set empty rule
        $valid->field('test');
        $this->assertTrue($valid->isValid());
    }

    /**
     * Test that using 'not' with empty rule does not cause runtime error.
     *
     * @return void
     */
    public function testItUsingNotWithEmptyRuleNotMakeRuntimeError(): void
    {
        $valid = new Validator(['test' => 'test']);
        // set empty rule with not
        $valid->field('test')->not();
        $this->assertTrue($valid->isValid());
    }
}
