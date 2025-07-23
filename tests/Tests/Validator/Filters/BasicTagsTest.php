<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test the behavior of the `basic_tags` filter.
 */
#[CoversClass(Validator::class)]
class BasicTagsTest extends TestCase
{
    /**
     * Test it can render the basic_tags filter rule.
     *
     * @return void
     */
    public function testItCanRenderBasicTags(): void
    {
        $this->assertEquals('basic_tags', fr()->basic_tags());
    }

    /**
     * Test it can filter input using the basic_tags rule.
     *
     * @return void
     */
    public function testItCanFilterBasicTags(): void
    {
        $validator = new Validator(['field' => '<script>link</script>']);
        $validator->filter('field')->basic_tags();

        $this->assertEquals(['field' => 'link'], $validator->filter_out());
    }
}
