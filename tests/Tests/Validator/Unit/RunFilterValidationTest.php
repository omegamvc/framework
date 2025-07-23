<?php

namespace Tests\Validator\Unit;

use Omega\Validator\Rule\FilterPool;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterPool::class)]
#[CoversClass(Validator::class)]
class RunFilterValidationTest extends TestCase
{
    public function testCanRunFilterUsingMethodFilterOut(): void
    {
        $valid = new Validator([
            'test1' => 'test',
            'test2' => 'test',
        ]);

        $valid->filter('test1')->upper_case();

        $this->assertEquals([
            'test1' => 'TEST',
            'test2' => 'test',
        ], $valid->filter_out());
    }

    public function testCanRunFilterUsingMethodFilterOutWithoutFilterRules(): void
    {
        $field = [
            'test1' => 'test',
            'test2' => 'test',
            'files' => [
                'test' => [
                    'name'  => 'test',
                    'error' => 4,
                ],
            ],
        ];

        $valid = new Validator($field);

        $this->assertEquals($field, $valid->filter_out());
    }

    public function testCanRunFilterUsingMethodFilterOutWithClosureParam(): void
    {
        $valid = new Validator([
            'test1' => 'test',
            'test2' => ' test ',
            'test3' => 'TEST',
            'test4' => ' test ',
            'test5' => ' test ',
            'test6' => ' test ',
            'test7' => ' test ',
        ]);

        $result = $valid->filter_out(function (FilterPool $pool) {
            $pool->rule('test1')->upper_case();
            $pool->test2->trim();
            $pool('test3')->lower_case();
            $pool->rule('test4', 'test5')->trim();
            $pool('test6', 'test7')->trim();
        });

        $this->assertEquals([
            'test1' => 'TEST',
            'test2' => 'test',
            'test3' => 'test',
            'test4' => 'test',
            'test5' => 'test',
            'test6' => 'test',
            'test7' => 'test',
        ], $result);
    }

    public function testCanRunFilterUsingMethodFilterOutWithClosureReturn(): void
    {
        $valid = new Validator([
            'test1' => 'test',
            'test2' => ' test ',
            'test3' => 'TEST',
            'test4' => ' test ',
            'test5' => ' test ',
            'test6' => ' test ',
            'test7' => ' test ',
        ]);

        $result = $valid->filter_out(function () {
            $pool = new FilterPool();
            $pool->rule('test1')->upper_case();
            $pool->test2->trim();
            $pool('test3')->lower_case();
            $pool->rule('test4', 'test5')->trim();
            $pool('test6', 'test7')->trim();

            return $pool;
        });

        $this->assertEquals([
            'test1' => 'TEST',
            'test2' => 'test',
            'test3' => 'test',
            'test4' => 'test',
            'test5' => 'test',
            'test6' => 'test',
            'test7' => 'test',
        ], $result);
    }

    public function testCanRunFilterUsingMethodFailedOrFilter(): void
    {
        $valid = new Validator(['test' => 'test']);

        $valid->field('test')->required();
        $valid->filter('test')->upper_case();

        $this->assertEquals(['test' => 'TEST'], $valid->failedOrFilter());
    }

    public function testCanRunFilterUsingMethodFailedOrFilterButNotValid(): void
    {
        $valid = new Validator(['test' => 'test']);

        $valid->field('test')->min_len(5);
        $valid->filter('test')->upper_case();

        $this->assertTrue($valid->failedOrFilter());
    }

    public function testCanGetFilterOutUsingFiltersProperty(): void
    {
        $valid = new Validator(['test' => 'test']);

        $valid->filter('test')->upper_case();

        $this->assertTrue($valid->filters->has('test'));
        $this->assertEquals('TEST', $valid->filters->get('test'));
    }
}
