<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test it can handle slug filter rules.
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class SlugTest extends TestCase
{
    /**
     * Test it can render the slug filter rule.
     *
     * @return void
     */
    public function testItCanRenderSlug(): void
    {
        $this->assertEquals('slug', fr()->slug());
    }

    /**
     * Test it can filter a string into a URL-friendly slug.
     *
     * @return void
     */
    public function testItCanFilterSlug(): void
    {
        $validator = new Validator(['field' => 'long title tobe url']);
        $validator->filter('field')->slug();

        $this->assertSame(['field' => 'long-title-tobe-url'], $validator->filter_out());
    }
}
