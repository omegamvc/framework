<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test the behavior of the `sanitize_email` filter rule.
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class SanitizeEmailTest extends TestCase
{
    /**
     * Test it can render the sanitize_email filter rule.
     *
     * @return void
     */
    public function testItCanRenderSanitizeEmail(): void
    {
        $this->assertEquals('sanitize_email', fr()->sanitize_email());
    }

    /**
     * Test it can filter and sanitize an email address.
     *
     * @return void
     */
    public function testItCanFilterSanitizeEmail(): void
    {
        $validator = new Validator(['field' => 'john(.doe)@exa//mple.com']);
        $validator->filter('field')->sanitize_email();

        $this->assertSame(
            ['field' => 'john.doe@example.com'],
            $validator->filter_out()
        );
    }
}
