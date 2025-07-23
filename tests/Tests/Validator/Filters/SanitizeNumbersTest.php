<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test it can handle sanitize_numbers filter rules.
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class SanitizeNumbersTest extends TestCase
{
    /**
     * Test it can render the sanitize_numbers filter rule.
     *
     * @return void
     */
    public function testItCanRenderSanitizeNumbers(): void
    {
        $this->assertEquals('sanitize_numbers', fr()->sanitize_numbers());
    }

    /**
     * Test it can filter and sanitize numbers from a string.
     *
     * @return void
     */
    public function testItCanFilterSanitizeNumbers(): void
    {
        $validator = new Validator(['field' => '5-2+3pp']);
        $validator->filter('field')->sanitize_numbers();

        $this->assertSame(['field' => '5-2+3'], $validator->filter_out());
    }
}
