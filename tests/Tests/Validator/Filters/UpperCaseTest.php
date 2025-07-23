<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test it can handle upper_case filter rules.
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class UpperCaseTest extends TestCase
{
    /**
     * Test it can render the upper_case filter rule.
     *
     * @return void
     */
    public function testItCanRenderUpperCase(): void
    {
        $this->assertEquals('upper_case', fr()->upper_case());
    }

    /**
     * Test it can filter a string by converting it to uppercase.
     *
     * @return void
     */
    public function testItCanFilterUpperCase(): void
    {
        $validator = new Validator(['field' => 'test']);
        $validator->filter('field')->upper_case();

        $this->assertSame(['field' => 'TEST'], $validator->filter_out());
    }
}
