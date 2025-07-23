<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test the behavior of the `ms_word_characters` filter rule.
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class MsWordCharactersTest extends TestCase
{
    /**
     * Test it can render the ms_word_characters filter rule.
     *
     * @return void
     */
    public function testItCanRenderMsWordCharacters(): void
    {
        $this->assertEquals('ms_word_characters', fr()->ms_word_characters());
    }

    /**
     * Test it can filter Microsoft Word special characters.
     *
     * @return void
     */
    public function testItCanFilterMsWordCharacters(): void
    {
        $validator = new Validator(['field' => '“test”,‘test’,–,…']);
        $validator->filter('field')->ms_word_characters();

        $this->assertSame(
            ['field' => '"test",\'test\',-,...'],
            $validator->filter_out()
        );
    }
}
