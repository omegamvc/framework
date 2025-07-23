<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test the behavior of the `lower_case` filter rule.
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class LowerCaseTest extends TestCase
{
    /**
     * Test it can render the lower_case filter rule.
     *
     * @return void
     */
    public function testItCanRenderLowerCase(): void
    {
        $this->assertEquals('lower_case', fr()->lower_case());
    }

    /**
     * Test it can apply the lower_case filter to a field value.
     *
     * @return void
     */
    public function testItCanFilterLowerCase(): void
    {
        $validator = new Validator(['field' => 'TEST']);
        $validator->filter('field')->lower_case();

        $this->assertSame(['field' => 'test'], $validator->filter_out());
    }
}
