<?php

declare(strict_types=1);

namespace Tests\Validator\Filters\Methods;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validator::class)]
class RawTest extends TestCase
{
    /**
     * Test it can add filter rule using raw.
     *
     * @return void
     */
    public function testItCanAddFilterRuleUsingRaw(): void
    {
        $validation = new Validator([
            'test' => 'test',
        ]);

        $validation->filter('test')->raw('upper_case');

        $this->assertEquals('TEST', $validation->filters->get('test'));
    }

    /**
     * Test it can add filter rule using raw combined with others.
     *
     * @return void
     */
    public function testItCanAddFilterRuleUsingRawCombinedWithOthers(): void
    {
        $validation = new Validator([
            'test' => ' test ',
        ]);

        $validation->filter('test')->raw('upper_case')->trim();

        $this->assertEquals('TEST', $validation->filters->get('test'));
    }
}
