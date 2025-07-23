<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test the behavior of the `rmpunctuation` filter rule.
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class RemovePunctuationTest extends TestCase
{
    /**
     * Test it can render the rmpunctuation filter rule.
     *
     * @return void
     */
    public function testItCanRenderRmpunctuation(): void
    {
        $this->assertEquals('rmpunctuation', fr()->rmpunctuation());
    }

    /**
     * Test it can filter punctuation characters from a string.
     *
     * @return void
     */
    public function testItCanFilterRmpunctuation(): void
    {
        $validator = new Validator(['field' => 'is true?']);
        $validator->filter('field')->rmpunctuation();

        $this->assertSame(['field' => 'is true'], $validator->filter_out());
    }
}
