<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;
use Throwable;

use function trim;

#[CoversClass(Templator::class)]
#[CoversClass(TemplatorFinder::class)]
final class ComponentTest extends TestCase
{
    /**
     * Test it can render component scope.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderComponentScope(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates(
            '{% component(\'component.template\') %}<main>core component</main>{% endcomponent %}'
        );
        $this->assertEquals('<html><head></head><body><main>core component</main></body></html>', trim($out));
    }

    /**
     * Test it can render nested component scope.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderNestedComponentScope(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates(
            '{% component(\'componentnested.template\') %}card with nest{% endcomponent %}'
        );
        $this->assertEquals(
            '<html><head></head><body><div class="card">card with nest</div>'
            . PHP_EOL
            . '</body></html>',
            trim($out)
        );
    }

    /**
     * Test it can render component scope multiple.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderComponentScopeMultiple(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates('{% component(\'componentcard.template\') %}oke{% endcomponent %} {% component(\'componentcard.template\') %}oke 2 {% endcomponent %}'); // phpcs:ignore
        $this->assertEquals(
            '<div class="card">oke</div>'
            . PHP_EOL
            . ' <div class="card">oke 2 </div>', trim($out)
        );
    }

    /**
     * Test it throw when extend not found.
     *
     * @return void
     * @throws Exception
     */
    public function testItThrowWhenExtendNotFound(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        try {
            $templator->templates(
                '{% component(\'notexits.template\') %}<main>core component</main>{% endcomponent %}'
            );
        } catch (Throwable $th) {
            $this->assertEquals(
                'View file not found: `notexits.template`', $th->getMessage()
            );
        }
    }

    /**
     * Test it throw when extend not found yield.
     *
     * @return void
     * @throws Exception
     */
    public function testItThrowWhenExtendNotFoundYield(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        try {
            $templator->templates(
                '{% component(\'componentyield.template\') %}<main>core component</main>{% endcomponent %}'
            );
        } catch (Throwable $th) {
            $this->assertEquals('Yield section not found: `component2.template`', $th->getMessage());
        }
    }

    /**
     * Test it can render component using named parameter.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderComponentUsingNamedParameter(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates(
            '{% component(\'componentnamed.template\', bg:\'bg-red\', size:"md") %}inner text{% endcomponent %}'
        );
        $this->assertEquals('<p class="bg-red md">inner text</p>', trim($out));
    }

    /**
     * Test it can render component opp a process.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderComponentOppAProcess(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $templator->setComponentNamespace('Tests\\View\\Templator\\');
        $out = $templator->templates(
            '{% component(\'TestClassComponent\', bg:\'bg-red\', size:"md") %}inner text{% endcomponent %}'
        );
        $this->assertEquals('<p class="bg-red md">inner text</p>', trim($out));
    }

    /**
     * Test it can get dependency view.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetDependencyView(): void
    {
        $finder    = new TemplatorFinder([__DIR__ . '/view/'], ['']);
        $templator = new Templator($finder, __DIR__);
        $templator->templates(
            '{% component(\'component.template\') %}<main>core component</main>{% endcomponent %}', 'test'
        );
        $this->assertEquals([
            $finder->find('component.template') => 1,
        ], $templator->getDependency('test'));
    }
}

class TestClassComponent
{
    private string $bg;
    private string $size;

    public function __construct(string $bg, string $size)
    {
        $this->bg   = $bg;
        $this->size = $size;
    }

    public function render(string $inner): string
    {
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        return "<p class=\"{$this->bg} {$this->size}\">{$inner}</p>";
    }
}
