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
final class BooleanTest extends TestCase
{
    /**
     * Test it can render boolean
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderBoolean(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<input x-enable="{% bool(1 == 1) %}">');
        $this->assertEquals(
            '<input x-enable="<?= (1 == 1) ? \'true\' : \'false\' ?>">',
            $out
        );
    }
}
