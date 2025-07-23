<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test it can render and filter whole numbers.
 *
 * @return void
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class WholeNumberTest extends TestCase
{
    /**
     * Test it can render the whole_number filter rule.
     *
     * @return void
     */
    public function testItCanRenderWholeNumber(): void
    {
        $this->assertEquals('whole_number', fr()->whole_number());
    }

    /**
     * Test it can filter and convert a string to a whole number.
     *
     * @return void
     */
    public function testItCanFilterWholeNumber(): void
    {
        $validator = new Validator(['field' => '123']);
        $validator->filter('field')->whole_number();

        $this->assertSame(['field' => 123], $validator->filter_out());
    }
}
