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
final class SetTest extends TestCase
{

    /**
     * Test it can render set string.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderSetString(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{% set $foo=\'bar\' %}');
        $this->assertEquals('<?php $foo = \'bar\'; ?>', $out);
    }


    /**
     * Test it can render set int.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderSetInt(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{% set $bar=123 %}');
        $this->assertEquals('<?php $bar = 123; ?>', $out);
    }

    /**
     * Test it can render set array.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderSetArray(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{% set $arr=[12, \'34\'] %}');
        $this->assertEquals('<?php $arr = [12, \'34\']; ?>', $out);
    }
}
