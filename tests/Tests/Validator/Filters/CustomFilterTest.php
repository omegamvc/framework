<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

/**
 * Test the behavior of custom filter callbacks in the filter chain.
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class CustomFilterTest extends TestCase
{
    /**
     * Test it can add a custom filter.
     *
     * @return void
     * @throws RandomException
     */
    public function testItCanAddCustomFilter(): void
    {
        $validation = new Validator(['test' => 'test']);

        $validation->filter('test')->filter(fn ($value, $param = []) => 'ok ' . $value);

        $this->assertSame(
            ['test' => 'ok test'],
            $validation->filters->all()
        );
    }

    /**
     * Test it can add a custom filter and combine it with another filter.
     *
     * @return void
     * @throws RandomException
     */
    public function testItCanAddCustomFilterCombineWithOther(): void
    {
        $validation = new Validator(['test' => 'test']);

        $validation->filter('test')
            ->filter(fn ($value, $param = []) => 'ok ' . $value)
            ->upper_case();

        $this->assertSame(
            ['test' => 'OK TEST'],
            $validation->filters->all()
        );
    }
}
