<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test it can handle trim filter rules.
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class TrimTest extends TestCase
{
    /**
     * Test it can render the trim filter rule.
     *
     * @return void
     */
    public function testItCanRenderTrim(): void
    {
        $this->assertEquals('trim', fr()->trim());
    }

    /**
     * Test it can filter a string by trimming whitespace.
     *
     * @return void
     */
    public function testItCanFilterTrim(): void
    {
        $validator = new Validator(['field' => '  nomore space  ']);
        $validator->filter('field')->trim();

        $this->assertSame(['field' => 'nomore space'], $validator->filter_out());
    }
}
