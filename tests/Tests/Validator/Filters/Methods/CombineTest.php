<?php

declare(strict_types=1);

namespace Tests\Validator\Filters\Methods;

use Omega\Validator\Rule\Filter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Filter::class)]
class CombineTest extends TestCase
{
    /**
     * Test it can combine filter class with other filter class.
     *
     * @return void
     */
    public function testItCanCombineFilterClassWithOtherFilterClass(): void
    {
        $filter = new Filter();
        $filter->trim();

        $filter2 = new Filter();
        $filter2->lower_case();
        $filter2->combine($filter);

        $this->assertEquals('lower_case|trim', $filter2->get_filter());
    }
}
