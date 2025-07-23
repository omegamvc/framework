<?php

declare(strict_types=1);

namespace Tests\Validator\Rule;

use Omega\Validator\Rule\ValidPool;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test it can add validation rules to the valid pool using various methods.
 */
#[CoversClass(ValidPool::class)]
#[CoversClass(Validator::class)]
final class ValidPoolTest extends TestCase
{
    /**
     * Test it can add valid using __get.
     *
     * @return void
     */
    public function testItCanAddValidUsingGet(): void
    {
        $pool = new ValidPool();

        $pool->test->required();

        $this->assertEquals([
            'test' => 'required',
        ], $pool->get_pool());
    }

    /**
     * Test it can add valid using __get with existing rule.
     *
     * @return void
     */
    public function testItCanAddValidUsingGetWithExistRule(): void
    {
        $pool = new ValidPool();

        $pool->test->required();
        $pool->test->alpha();

        $this->assertEquals([
            'test' => 'required|alpha',
        ], $pool->get_pool());
    }

    /**
     * Test it can add valid using __set.
     *
     * @return void
     */
    public function testItCanAddValidUsingSet(): void
    {
        $pool = new ValidPool();

        $pool->test = 'required';

        $this->assertEquals([
            'test' => 'required',
        ], $pool->get_pool());
    }

    /**
     * Test it can add valid using __set with existing rule.
     *
     * @return void
     */
    public function testItCanAddValidUsingSetWithExistRule(): void
    {
        $pool = new ValidPool();

        $pool->test = 'required';
        $pool->test = 'alpha';

        $this->assertEquals([
            'test' => 'required|alpha',
        ], $pool->get_pool());
    }

    /**
     * Test it can add valid using __invoke.
     *
     * @return void
     */
    public function testItCanAddValidUsingInvoke(): void
    {
        $pool = new ValidPool();

        $pool('test')->required();

        $this->assertEquals([
            'test' => 'required',
        ], $pool->get_pool());
    }

    /**
     * Test it can add valid using __invoke with existing rule.
     *
     * @return void
     */
    public function testItCanAddValidUsingInvokeWithExistRule(): void
    {
        $pool = new ValidPool();

        $pool('test')->required();
        $pool('test')->alpha();

        $this->assertEquals([
            'test' => 'required|alpha',
        ], $pool->get_pool());
    }

    /**
     * Test it can add valid using __invoke with multiple fields.
     *
     * @return void
     */
    public function testItCanAddValidUsingInvokeMulti(): void
    {
        $pool = new ValidPool();

        $pool('test', 'test2')->required();

        $this->assertEquals([
            'test'  => 'required',
            'test2' => 'required',
        ], $pool->get_pool());
    }

    /**
     * Test it can add valid using rule method.
     *
     * @return void
     */
    public function testItCanAddValidUsingRule(): void
    {
        $pool = new ValidPool();

        $pool->rule('test')->required();

        $this->assertEquals([
            'test' => 'required',
        ], $pool->get_pool());
    }

    /**
     * Test it can add valid using rule method with existing rule.
     *
     * @return void
     */
    public function testItCanAddValidUsingRuleWithExistRule(): void
    {
        $pool = new ValidPool();

        $pool->rule('test')->required();
        $pool->rule('test')->alpha();

        $this->assertEquals([
            'test' => 'required|alpha',
        ], $pool->get_pool());
    }

    /**
     * Test it can add valid using rule method with multiple fields.
     *
     * @return void
     */
    public function testItCanAddValidUsingRuleMulti(): void
    {
        $pool = new ValidPool();

        $pool->rule('test', 'test2')->required();

        $this->assertEquals([
            'test'  => 'required',
            'test2' => 'required',
        ], $pool->get_pool());
    }
}
