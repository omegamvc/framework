<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use PHPUnit\Framework\TestCase;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;

final class IncludeTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCanRenderInclude()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body>{% include(\'/view/component.php\') %}</body></html>');
        $this->assertEquals('<html><head></head><body><p>Call From Component</p></body></html>', $out);
    }

    /**
     * @return void
     */
    public function testItCanFetDependencyView()
    {
        $finder    = new TemplatorFinder([__DIR__], ['']);
        $templator = new Templator($finder, __DIR__);
        $templator->templates('<html><head></head><body>{% include(\'view/component.php\') %}</body></html>', 'test');
        $this->assertEquals([
            $finder->find('view/component.php') => 1,
        ], $templator->getDependency('test'));
    }
}
