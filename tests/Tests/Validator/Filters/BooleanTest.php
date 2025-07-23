<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test the behavior of the `boolean` filter rule.
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class BooleanTest extends TestCase
{
    /**
     * Test it can render the boolean filter rule.
     *
     * @return void
     */
    public function testItCanRenderBoolean(): void
    {
        $this->assertEquals('boolean', fr()->boolean());
    }

    /**
     * Test it can filter different truthy values into boolean `true`.
     *
     * @return void
     */
    public function testItCanFilterBoolean(): void
    {
        $validator = new Validator([
            'field_1' => '1',
            'field_2' => 1,
            'field_3' => 'true',
            'field_4' => 'yes',
            'field_5' => 'on',
        ]);

        $validator->filter('field_1')->boolean();
        $validator->filter('field_2')->boolean();
        $validator->filter('field_3')->boolean();
        $validator->filter('field_4')->boolean();
        $validator->filter('field_5')->boolean();

        $this->assertEquals([
            'field_1' => true,
            'field_2' => true,
            'field_3' => true,
            'field_4' => true,
            'field_5' => true,
        ], $validator->filter_out());
    }
}
