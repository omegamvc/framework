<?php

declare(strict_types=1);

namespace Tests\Validator\Validations\Methods;

use Exception;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test it can conditionally execute validation methods using the `if` modifier.
 */
#[CoversClass(Validator::class)]
class IfValidMethodTest extends TestCase
{
    /**
     * Test it can execute method using if (true).
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfTrue(): void
    {
        $val = new Validator();

        $val->field('test')->if(fn () => true)->required();

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can execute method using if (false).
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfFalse(): void
    {
        $val = new Validator();

        $val->field('test')->if(fn () => false)->required();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can execute method using if (true, true).
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfTrueTrue(): void
    {
        $val = new Validator(['test' => '200']);

        $val->field('test')->if(fn () => true)->numeric()->if(fn () => true)->min_len(2);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can execute method using if (true, false).
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfTrueFalse(): void
    {
        $val = new Validator(['test' => '1']);

        $val->field('test')->if(fn () => true)->numeric()->if(fn () => false)->min_len(2);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can execute method using if (false, true).
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfFalseTrue(): void
    {
        $val = new Validator(['test' => 'test']);

        $val->field('test')->if(fn () => false)->numeric()->if(fn () => true)->min_len(2);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can execute method using if (false, false).
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfFalseFalse(): void
    {
        $val = new Validator(['test' => 'text']);

        $val->field('test')->if(fn () => false)->numeric()->if(fn () => false)->min_len(2);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can execute method using consecutive if(true, true).
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfContinueTrueTrue(): void
    {
        $val = new Validator(['test' => 'test']);

        $val->field('test')->if(fn () => true)->if(fn () => true)->required();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can execute method using consecutive if(true, false).
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfContinueTrueFalse(): void
    {
        $val = new Validator(['test' => 'text']);

        $val->field('test')->if(fn () => true)->if(fn () => false)->alpha();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can execute method using consecutive if(false, false).
     *
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfContinueFalseFalse(): void
    {
        $val = new Validator(['test' => 'text']);

        $val->field('test')->if(fn () => false)->if(fn () => false)->min_len(2);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can execute method using consecutive if(false, true).
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfContinueFalseTrue(): void
    {
        $val = new Validator(['test' => 'text']);

        $val->field('test')->if(fn () => false)->if(fn () => true)->min_len(2);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can validate using if combined with submitted.
     *
     *
     * @return void
     * @throws Exception
     */
    public function testItCanValidateCombineWithSubmittedMethod(): void
    {
        $val = new Validator();

        $val->field('test')->if(fn () => $val->submitted())->required();

        $this->assertTrue($val->isValid());
    }
}
