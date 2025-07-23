<?php

namespace Tests\Validator\Unit;

use Omega\Validator\Rule\FilterPool;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test adding filter rules in various ways.
 */
#[CoversClass(FilterPool::class)]
#[CoversClass(Validator::class)]
class AddFilterRuleValidationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test adding a filter rule using filter method.
     *
     * @return void
     */
    public function testItCanAddFilterRule(): void
    {
        $validation = new Validator(['test' => 'test']);
        $validation->filter('test')->upper_case();

        $this->assertEquals('TEST', $validation->filters->get('test'));
    }

    /**
     * Test adding filter rule using filters method with param callback.
     *
     * @return void
     */
    public function testItCanAddFilterRuleUsingFiltersMethodParam(): void
    {
        $valid = new Validator(['test' => ' test ', 'test2' => ' test ']);

        $valid->field('test', 'test2')->required();
        $valid->filters(fn (FilterPool $f) => [
            $f->test->trim(),
            $f->test2->trim(),
        ]);

        $this->assertEquals(
            ['test' => 'test', 'test2' => 'test'],
            $valid->filter_out()
        );
    }

    /**
     * Test adding filter rule using filters method with return callback.
     *
     * @return void
     */
    public function testItCanAddFilterRuleUsingFiltersMethodReturn(): void
    {
        $valid = new Validator(['test' => ' test ', 'test2' => ' test ']);

        $valid->field('test', 'test2')->required();
        $valid->filters(function () {
            $f = new FilterPool();
            $f->test->trim();
            $f->test2->trim();

            return $f;
        });

        $this->assertEquals(
            ['test' => 'test', 'test2' => 'test'],
            $valid->filter_out()
        );
    }

    /**
     * Test adding multiple filters on existing rules.
     *
     * @return void
     */
    public function testItCanAddNewFilterWithExistingRule(): void
    {
        $valid = new Validator(['test' => ' test ']);

        $valid->field('test')->required();

        $valid->filter('test')->trim();
        $valid->filter('test')->upper_case();

        $this->assertEquals(
            ['test' => 'TEST'],
            $valid->filter_out()
        );
    }

    /**
     * Test adding multiple filters with method filters on existing rules.
     *
     * @return void
     */
    public function testItCanAddNewFilterWithExistingRuleUsingFiltersMethod(): void
    {
        $valid = new Validator(['test' => ' test ']);

        $valid->field('test')->required();

        $valid->filters(fn (FilterPool $filter) => [
            $filter('test')->trim(),
            $filter('test')->upper_case(),
        ]);

        $this->assertEquals(
            ['test' => 'TEST'],
            $valid->filter_out()
        );
    }

    /**
     * Test adding filter rule using pools callback from Validator::make() with param callback.
     *
     * @return void
     */
    public function testItCanAddFilterRuleUsingPoolsCallbackFromMakeParam(): void
    {
        $v = Validator::make(
            ['test' => 'test'],
            null,
            fn (FilterPool $f) => [
                $f('test')->upper_case(),
            ]
        );

        $this->assertEquals('TEST', $v->filters->get('test'));
    }

    /**
     * Test adding filter rule using pools callback from Validator::make() with return callback.
     *
     * @return void
     */
    public function testItCanAddFilterRuleUsingPoolsCallbackFromMakeReturn(): void
    {
        $v = Validator::make(
            ['test' => 'Test'],
            null,
            function () {
                $f = new FilterPool();
                $f('test')->upper_case();

                return $f;
            }
        );

        $this->assertEquals('TEST', $v->filters->get('test'));
    }

    /**
     * Test adding multiple filters using method filter for multiple fields.
     *
     * @return void
     */
    public function testItCanAddMultipleFiltersUsingFilterMethod(): void
    {
        $valid = new Validator(['test' => ' test ', 'test2' => ' test ']);

        $valid->field('test', 'test2')->required();
        $valid->filter('test', 'test2')->trim();

        $this->assertEquals(
            ['test' => 'test', 'test2' => 'test'],
            $valid->filter_out()
        );
    }

    /**
     * Test adding multiple filters using method filter with existing filters.
     *
     * @return void
     */
    public function testItCanAddMultipleFiltersUsingFilterMethodWithExistingFilters(): void
    {
        $valid = new Validator(['test' => ' test? ', 'test2' => ' test ']);

        $valid->field('test', 'test2')->required();
        $valid->filter('test', 'test2')->trim();
        $valid->filter('test')->upper_case();
        $valid->filter('test2')->upper_case();
        $valid->filter('test', 'test2')->rmpunctuation();

        $this->assertEquals(
            ['test' => 'TEST', 'test2' => 'TEST'],
            $valid->filter_out()
        );
    }
}
