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
final class IncludeTest extends TestCase
{
    /**
     * Test it can render include.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderInclude(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body>{% include(\'/view/component.php\') %}</body></html>');
        $this->assertEquals('<html><head></head><body><p>Call From Component</p></body></html>', $out);
    }

    /**
     * Test it can fetch dependency view.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanFetchDependencyView(): void
    {
        $finder    = new TemplatorFinder([__DIR__], ['']);
        $templator = new Templator($finder, __DIR__);
        $templator->templates('<html><head></head><body>{% include(\'view/component.php\') %}</body></html>', 'test');
        $this->assertEquals([
            $finder->find('view/component.php') => 1,
        ], $templator->getDependency('test'));
    }
}
