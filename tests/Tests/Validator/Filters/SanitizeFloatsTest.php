<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test it can handle sanitize_floats filter rules.
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class SanitizeFloatsTest extends TestCase
{
    /**
     * Test it can render the sanitize_floats filter rule.
     *
     * @return void
     */
    public function testItCanRenderSanitizeFloats(): void
    {
        $this->assertEquals('sanitize_floats', fr()->sanitize_floats());
    }

    /**
     * Test it can filter and convert to float.
     *
     * @return void
     */
    public function testItCanFilterSanitizeFloats(): void
    {
        $validator = new Validator(['field' => '12.3']);
        $validator->filter('field')->sanitize_floats();

        $this->assertEquals(['field' => 12.3], $validator->filter_out());
    }
}
