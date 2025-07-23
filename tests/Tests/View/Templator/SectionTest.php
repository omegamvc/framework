<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use PHPUnit\Framework\TestCase;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;

final class SectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCanRenderSectionScope()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates('{% extend(\'section.template\') %} {% section(\'title\') %}<strong>taylor</strong>{% endsection %}');
        $this->assertEquals('<p><strong>taylor</strong></p>', trim($out));
    }

    /**
     * @return void
     */
    public function testItThrowWhenExtendNotFound()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        try {
            $templator->templates('{% extend(\'section.html\') %} {% section(\'title\') %}<strong>taylor</strong>{% endsection %}');
        } catch (\Throwable $th) {
            $this->assertEquals('Template file not found: section.html', $th->getMessage());
        }
    }

    /**
     * @return void
     */
    public function testItCanRenderSectionInline()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates('{% extend(\'section.template\') %} {% section(\'title\', \'taylor\') %}');
        $this->assertEquals('<p>taylor</p>', trim($out));
    }

    /**
     * @return void
     */
    public function testItCanRenderSectionInlineEscape()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates('{% extend(\'section.template\') %} {% section(\'title\', \'<script>alert(1)</script>\') %}');
        $this->assertEquals('<p>&lt;script&gt;alert(1)&lt;/script&gt;</p>', trim($out));
    }

    /**
     * @return void
     */
    public function testItCanRenderMultySection()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates('
            {% extend(\'section.template\') %}

            {% sections %}
            title : <strong>taylor</strong>
            {% endsections %}
        ');
        $this->assertEquals('<p><strong>taylor</strong></p>', trim($out));
    }

    /**
     * @return void
     */
    public function testItCanGetDependencyView()
    {
        $finder    = new TemplatorFinder([__DIR__ . '/view/'], ['']);
        $templator = new Templator($finder, __DIR__);
        $templator->templates('{% extend(\'section.template\') %} {% section(\'title\') %}<strong>taylor</strong>{% endsection %}', 'test');
        $this->assertEquals([
            $finder->find('section.template') => 1,
        ], $templator->getDependency('test'));
    }

    /**
     * @return void
     */
    public function testItCanRenderSectionScopeWithDefaultYeild()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates('{% extend(\'sectiondefault.template\') %}');
        $this->assertEquals('<p>nuno</p>', trim($out));
    }

    /**
     * @return void
     */
    public function testItCanRenderSectionWithMulyLine()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates('{% extend(\'sectiondefaultmultylines.template\') %}');
        $this->assertEquals('<li>' . PHP_EOL . '<ul>one</ul>' . PHP_EOL . '<ul>two</ul>' . PHP_EOL . '<ul>three</ul>' . PHP_EOL . '</li>', trim($out));
    }

    /**
     * @return void
     */
    public function testItWillThrowErrorHaveTwoDefault()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $this->expectExceptionMessage('The yield statement cannot have both a default value and content.');
        $templator->templates('{% extend(\'sectiondefaultandmultylines.template\') %}');
    }
}
