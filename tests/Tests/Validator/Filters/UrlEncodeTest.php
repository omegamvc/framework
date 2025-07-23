<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test it can render and filter url encoding.
 *
 * @return void
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class UrlEncodeTest extends TestCase
{
    /**
     * Test it can render the urlencode filter rule.
     *
     * @return void
     */
    public function testItCanRenderUrlEncode(): void
    {
        $this->assertEquals('urlencode', fr()->urlencode());
    }

    /**
     * Test it can filter a string by applying URL encoding.
     *
     * @return void
     */
    public function testItCanFilterUrlEncode(): void
    {
        $validator = new Validator(['field' => 'test.com/true/right?one=1#2']);
        $validator->filter('field')->urlencode();

        $this->assertSame(['field' => 'test.com%2Ftrue%2Fright%3Fone%3D1%232'], $validator->filter_out());
    }
}
