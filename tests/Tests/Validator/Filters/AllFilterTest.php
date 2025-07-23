<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Filter::class)]
class AllFilterTest extends TestCase
{
    /**
     * Test it can render all filter string using chain method.
     *
     * @return void
     */
    public function testItCanRenderAllFilterStringUsingChainMethod(): void
    {
        $rule = new Filter();

        $rule
            ->noise_words()
            ->rmpunctuation()
            ->urlencode()
            ->sanitize_email()
            ->sanitize_numbers()
            ->sanitize_floats()
            ->sanitize_string()
            ->boolean()
            ->basic_tags()
            ->whole_number()
            ->ms_word_characters()
            ->lower_case()
            ->upper_case()
            ->slug()
            ->trim();

        $expected = 'noise_words|rmpunctuation|urlencode|sanitize_email|sanitize_numbers|sanitize_floats|sanitize_string|boolean|basic_tags|whole_number|ms_word_characters|lower_case|upper_case|slug|trim';

        $this->assertEquals($expected, $rule);
    }
}
