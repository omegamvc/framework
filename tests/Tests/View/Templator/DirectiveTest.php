<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use PHPUnit\Framework\TestCase;
use Omega\View\Exceptions\DirectiveCanNotBeRegister;
use Omega\View\Exceptions\DirectiveNotRegister;
use Omega\View\Templator;
use Omega\View\Templator\DirectiveTemplator;
use Omega\View\TemplatorFinder;

final class DirectiveTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCanRenderEachBreak()
    {
        DirectiveTemplator::register('sum', fn ($a, $b): int => $a + $b);
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body>{% sum(1, 2) %}</body></html>');
        $this->assertEquals("<html><head></head><body><?php echo Omega\View\Templator\DirectiveTemplator::call('sum', 1, 2); ?></body></html>", $out);
    }

    /**
     * @return void
     */
    public function testItThowExcaptionDueDirectiveNotRegister()
    {
        $this->expectException(DirectiveNotRegister::class);
        DirectiveTemplator::call('unknow', 0);
    }

    public function testItCanNotRegisterDirective(): void
    {
        $this->expectException(DirectiveCanNotBeRegister::class);
        DirectiveTemplator::register('include', fn ($file): string => $file);
    }

    public function testItCanRegisterAndCallDirective(): void
    {
        DirectiveTemplator::register('sum', fn ($a, $b): int => $a + $b);
        $this->assertEquals(2, DirectiveTemplator::call('sum', 1, 1));
    }
}
