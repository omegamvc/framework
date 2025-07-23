<?php

declare(strict_types=1);

namespace Tests\Validator\Filters\Methods;

use Exception;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

#[CoversClass(Validator::class)]
class WhereTest extends TestCase
{
    /**
     * Test it can render filter rule using where true.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderFilterRuleUsingWhereTrue(): void
    {
        $this->assertEquals('trim', fr()->trim()->where(fn () => true));
    }

    /**
     * Test it can render filter rule using where false.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderFilterRuleUsingWhereFalse(): void
    {
        $this->assertEquals('', fr()->trim()->where(fn () => false));
    }

    /**
     * Test it can reset filter rule using where true.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanResetFilterRuleUsingWhereTrue(): void
    {
        $val = new Validator(['test' => ' trim ']);

        $val->filter('test')->trim()->where(fn () => true);

        $this->assertSame(['test' => 'trim'], $val->filter_out());
    }

    /**
     * Test it can reset filter rule using where false.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanResetFilterRuleUsingWhereFalse(): void
    {
        $val = new Validator(['test' => ' trim ']);

        $val->filter('test')->trim()->where(fn () => false);

        $this->assertSame(['test' => ' trim '], $val->filter_out());
    }

    /**
     * Test it throws if where condition has no return.
     *
     * @return void
     * @throws Exception
     */
    public function testItThrowsIfWhereConditionHasNoReturn(): void
    {
        $val = new Validator(['test' => ' trim ']);

        $this->expectExceptionMessage('Condition closure not return boolean');

        $val->filter('test')->trim()->where(function () {
        });
    }

    /**
     * Test it throws if where condition returns non-boolean.
     *
     * @return void
     * @throws Exception
     */
    public function testItThrowsIfWhereConditionReturnsNonBoolean(): void
    {
        $val = new Validator(['test' => ' trim ']);

        $this->expectExceptionMessage('Condition closure not return boolean');

        $val->filter('test')->trim()->where(fn () => 'test');
    }

    /**
     * Test it can execute rule combined with submitted method.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanExecuteRuleCombinedWithSubmittedMethod(): void
    {
        $val = new Validator(['test' => ' trim ']);

        $val->filter('test')->trim()->where(fn () => $val->submitted());

        $this->assertSame(['test' => ' trim '], $val->filter_out());
    }
}
