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
final class ParentDataTest extends TestCase
{
    /**
     * Test it render parent data.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderParentData(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>my name is {{ $__[\'full.name\'] }} </h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1>my name is <?php echo htmlspecialchars($__[\'full.name\']); ?> </h1></body></html>', $out);
    }
}
