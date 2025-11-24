<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;

#[CoversClass(Templator::class)]
#[CoversClass(TemplatorFinder::class)]
final class EachBreakTest extends TestCase
{
    /**
     * Test it can render each break.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderEachBreak(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates(
            '<html><head></head><body>{% foreach ($numbers as $number) %}{% break %}{% endforeach %}</body></html>'
        );
        $this->assertEquals('<html><head></head><body><?php foreach ($numbers as $number): ?><?php break ; ?><?php endforeach; ?></body></html>', $out);
    }
}
