<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test the behavior of the `htmlencode` filter rule.
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class HtmlEncodeTest extends TestCase
{
    /**
     * Test it can render the htmlencode filter rule.
     *
     * @return void
     */
    public function testItCanRenderHtmlencode(): void
    {
        $this->assertEquals('htmlencode', fr()->htmlencode());
    }

    /**
     * Test it can filter content using the htmlencode rule.
     *
     * @return void
     */
    public function testItCanFilterHtmlencode(): void
    {
        $validator = new Validator(['field' => '<html>html tag</html>']);

        $validator->filter('field')->htmlencode();

        $this->assertSame(
            ['field' => '&#60;html&#62;html tag&#60;/html&#62;'],
            $validator->filter_out()
        );
    }
}
