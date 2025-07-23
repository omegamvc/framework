<?php

declare(strict_types=1);

namespace Tests\Validator\Filters\Methods;

use Exception;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validator::class)]
class IfMethodTest extends TestCase
{
    /**
     * Test it can execute method using if true.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfTrue(): void
    {
        $val = new Validator(['test' => ' trim ']);
        $val->filter('test')->if(fn () => true)->trim();

        $this->assertSame(['test' => 'trim'], $val->filter_out());
    }

    /**
     * Test it can execute method using if false.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfFalse(): void
    {
        $val = new Validator(['test' => ' trim ']);
        $val->filter('test')->if(fn () => false)->trim();

        $this->assertSame(['test' => ' trim '], $val->filter_out());
    }

    /**
     * Test it can execute method using if true/true.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfTrueTrue(): void
    {
        $val = new Validator(['test' => ' trim ']);
        $val->filter('test')->if(fn () => true)->trim()->if(fn () => true)->upper_case();

        $this->assertSame(['test' => 'TRIM'], $val->filter_out());
    }

    /**
     * test it can execute method using if true false.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfTrueFalse(): void
    {
        $val = new Validator(['test' => ' trim ']);
        $val->filter('test')->if(fn () => true)->trim()->if(fn () => false)->upper_case();

        $this->assertSame(['test' => 'trim'], $val->filter_out());
    }

    /**
     * Test it can execute method using if false true.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfFalseTrue(): void
    {
        $val = new Validator(['test' => ' trim ']);
        $val->filter('test')->if(fn () => false)->trim()->if(fn () => true)->upper_case();

        $this->assertSame(['test' => ' TRIM '], $val->filter_out());
    }

    /**
     * Test it can execute method using if false/false.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfFalseFalse(): void
    {
        $val = new Validator(['test' => ' trim ']);
        $val->filter('test')->if(fn () => false)->trim()->if(fn () => false)->upper_case();

        $this->assertSame(['test' => ' trim '], $val->filter_out());
    }

    /**
     * Test it can execute method using if continue true/true.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfContinueTrueTrue(): void
    {
        $val = new Validator(['test' => ' trim ']);
        $val->filter('test')->if(fn () => true)->if(fn () => true)->trim();

        $this->assertSame(['test' => 'trim'], $val->filter_out());
    }

    /**
     * Test it can execute method using if continue true false.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfContinueTrueFalse(): void
    {
        $val = new Validator(['test' => ' trim ']);
        $val->filter('test')->if(fn () => true)->if(fn () => false)->trim();

        $this->assertSame(['test' => ' trim '], $val->filter_out());
    }

    /**
     * Test it can execute method using id continue false/false.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfContinueFalseFalse(): void
    {
        $val = new Validator(['test' => ' trim ']);
        $val->filter('test')->if(fn () => false)->if(fn () => false)->trim();

        $this->assertSame(['test' => ' trim '], $val->filter_out());
    }

    /**
     * Test it can execute method using if continue false true.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteMethodUsingIfContinueFalseTrue(): void
    {
        $val = new Validator(['test' => ' trim ']);
        $val->filter('test')->if(fn () => false)->if(fn () => true)->trim();

        $this->assertSame(['test' => 'trim'], $val->filter_out());
    }

    /**
     * Test it can execute role combined with submitted method.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteRuleCombinedWithSubmittedMethod(): void
    {
        $val = new Validator(['test' => ' trim ']);
        $val->filter('test')->if(fn () => $val->submitted())->trim();

        $this->assertSame(['test' => ' trim '], $val->filter_out());
    }
}
