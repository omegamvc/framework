<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test it can handle sanitize_string filter rules.
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class SanitizeStringTest extends TestCase
{
    /**
     * Test it can render the sanitize_string filter rule.
     *
     * @return void
     */
    public function testItCanRenderSanitizeString(): void
    {
        $this->assertEquals('sanitize_string', fr()->sanitize_string());
    }

    /**
     * Test it can filter and sanitize string by stripping HTML tags.
     *
     * @return void
     */
    public function testItCanFilterSanitizeString(): void
    {
        $validator = new Validator(['field' => '<h1>Hello World!</h1>']);
        $validator->filter('field')->sanitize_string();

        $this->assertSame(['field' => 'Hello World!'], $validator->filter_out());
    }
}
