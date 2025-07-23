<?php

declare(strict_types=1);

namespace Tests\Validator\Rule;

use Omega\Validator\Rule\FilterPool;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test it can add filter rules to the filter pool using various methods.
 *
 * @return void
 */
#[CoversClass(FilterPool::class)]
#[CoversClass(Validator::class)]
final class FilterPoolTest extends TestCase
{
    /**
     * Test it can add filter pool using __get.
     *
     * @return void
     */
    public function testItCanAddFilterPoolUsingGet(): void
    {
        $pool = new FilterPool();

        $pool->test->trim();

        $this->assertEquals([
            'test' => 'trim',
        ], $pool->get_pool());
    }

    /**
     * Test it can add filter pool using __get with existing rules.
     *
     * @return void
     */
    public function testItCanAddFilterPoolUsingGetWithExistingRule(): void
    {
        $pool = new FilterPool();

        $pool->test->trim();
        $pool->test->upper_case();

        $this->assertEquals([
            'test' => 'trim|upper_case',
        ], $pool->get_pool());
    }

    /**
     * Test it can add filter pool using __set.
     *
     * @return void
     */
    public function testItCanAddFilterPoolUsingSet(): void
    {
        $pool = new FilterPool();

        $pool->test = 'trim';

        $this->assertEquals([
            'test' => 'trim',
        ], $pool->get_pool());
    }

    /**
     * Test it can add filter pool using __set with existing rules.
     *
     * @return void
     */
    public function testItCanAddFilterPoolUsingSetWithExistingRule(): void
    {
        $pool = new FilterPool();

        $pool->test = 'trim';
        $pool->test = 'upper_case';

        $this->assertEquals([
            'test' => 'trim|upper_case',
        ], $pool->get_pool());
    }

    /**
     * Test it can add filter pool using __invoke.
     *
     * @return void
     */
    public function testItCanAddFilterPoolUsingInvoke(): void
    {
        $pool = new FilterPool();

        $pool('test')->trim();

        $this->assertEquals([
            'test' => 'trim',
        ], $pool->get_pool());
    }

    /**
     * Test it can add filter pool using __invoke with multiple fields.
     *
     * @return void
     */
    public function testItCanAddFilterPoolUsingInvokeMulti(): void
    {
        $pool = new FilterPool();

        $pool('test', 'test2')->trim();

        $this->assertEquals([
            'test'  => 'trim',
            'test2' => 'trim',
        ], $pool->get_pool());
    }

    /**
     * Test it can add filter pool using __invoke with existing rules.
     *
     * @return void
     */
    public function testItCanAddFilterPoolUsingInvokeWithExistingRule(): void
    {
        $pool = new FilterPool();

        $pool('test', 'test2')->trim();
        $pool('test')->upper_case();
        $pool('test2')->upper_case();

        $this->assertEquals([
            'test'  => 'trim|upper_case',
            'test2' => 'trim|upper_case',
        ], $pool->get_pool());
    }

    /**
     * Test it can add filter pool using rule method.
     *
     * @return void
     */
    public function testItCanAddFilterPoolUsingRule(): void
    {
        $pool = new FilterPool();

        $pool->rule('test')->trim();

        $this->assertEquals([
            'test' => 'trim',
        ], $pool->get_pool());
    }

    /**
     * Test it can add filter pool using rule method with multiple fields.
     *
     * @return void
     */
    public function testItCanAddFilterPoolUsingRuleMulti(): void
    {
        $pool = new FilterPool();

        $pool->rule('test', 'test2')->trim();

        $this->assertEquals([
            'test'  => 'trim',
            'test2' => 'trim',
        ], $pool->get_pool());
    }

    /**
     * Test it can add filter pool using rule method with existing rules.
     *
     * @return void
     */
    public function testItCanAddFilterPoolUsingRuleMultiWithExistingRule(): void
    {
        $pool = new FilterPool();

        $pool->rule('test', 'test2')->trim();
        $pool->rule('test')->upper_case();
        $pool->rule('test2')->upper_case();

        $this->assertEquals([
            'test'  => 'trim|upper_case',
            'test2' => 'trim|upper_case',
        ], $pool->get_pool());
    }
}
