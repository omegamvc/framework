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
final class EachContinueTest extends TestCase
{
    /**
     * Test it can render each continue.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderEachContinue(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{% foreach ($numbers as $number) %}{% continue %}{% endforeach %}');
        $this->assertEquals('<?php foreach ($numbers as $number): ?><?php continue ; ?><?php endforeach; ?>', $out);
    }
}
