<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use PHPUnit\Framework\TestCase;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;

final class NamingTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCanRenderNaming()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>your {{ $name }}, ages {{ $age }} </h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1>your <?php echo htmlspecialchars($name ); ?>, ages <?php echo htmlspecialchars($age ); ?> </h1></body></html>', $out);
    }

    /**
     * @return void
     */
    public function testItCanRenderNamingWithCallFunction()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>time: }{{ now()->timestamp }}</h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1>time: }<?php echo htmlspecialchars(now()->timestamp ); ?></h1></body></html>', $out);
    }

    /**
     * @return void
     */
    public function testItCanRenderNamingTernary()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>your {{ $name ?? \'nuno\' }}, ages {{ $age ? 17 : 28 }} </h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1>your <?php echo htmlspecialchars($name ?? \'nuno\' ); ?>, ages <?php echo htmlspecialchars($age ? 17 : 28 ); ?> </h1></body></html>', $out);
    }

    /**
     * @return void
     */
    public function testItCanRenderNamingSkip()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>{{ $render }}, {% raw %}your {{ name }}, ages {{ age }}{% endraw %}</h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1><?php echo htmlspecialchars($render ); ?>, your {{ name }}, ages {{ age }}</h1></body></html>', $out);
    }
}
