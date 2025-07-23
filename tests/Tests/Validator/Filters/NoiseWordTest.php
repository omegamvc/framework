<?php

declare(strict_types=1);

namespace Tests\Validator\Filters;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\fr;

/**
 * Test the behavior of the `noise_words` filter rule.
 */
#[CoversClass(Filter::class)]
#[CoversClass(Validator::class)]
class NoiseWordTest extends TestCase
{
    /**
     * Test it can render the noise_words filter rule.
     *
     * @return void
     */
    public function testItCanRenderNoiseWords(): void
    {
        $this->assertEquals('noise_words', fr()->noise_words());
    }

    /**
     * Test it can filter input using the noise_words rule.
     *
     * @return void
     */
    public function testItCanFilterNoiseWords(): void
    {
        $validator = new Validator(['field' => 'word']);
        $validator->filter('field')->noise_words();

        $this->assertSame(['field' => 'word'], $validator->filter_out());
    }
}
