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
final class NamingTest extends TestCase
{
    /**
     * Test it can render naming.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderNaming(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>your {{ $name }}, ages {{ $age }} </h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1>your <?php echo htmlspecialchars($name); ?>, ages <?php echo htmlspecialchars($age); ?> </h1></body></html>', $out);
    }

    /**
     * Test it can render naming without escape.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderNamingWithoutEscape(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>your {!! $name !!}, ages {!! $age !!} </h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1>your <?php echo $name ; ?>, ages <?php echo $age ; ?> </h1></body></html>', $out);
    }

    /**
     * Test it can render naming with call function.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderNamingWithCallFunction(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>time: }{{ now()->timestamp }}</h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1>time: }<?php echo htmlspecialchars(now()->timestamp); ?></h1></body></html>', $out);
    }

    /**
     * Test it can render naming ternary.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderNamingTernary(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>your {{ $name ?? \'nuno\' }}, ages {{ $age ? 17 : 28 }} </h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1>your <?php echo htmlspecialchars($name ?? \'nuno\'); ?>, ages <?php echo htmlspecialchars($age ? 17 : 28); ?> </h1></body></html>', $out);
    }

    /**
     * Test it can render naming skip.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderNamingSkip(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>{{ $render }}, {% raw %}your {{ name }}, ages {{ age }}{% endraw %}</h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1><?php echo htmlspecialchars($render); ?>, your {{ name }}, ages {{ age }}</h1></body></html>', $out);
    }
}
