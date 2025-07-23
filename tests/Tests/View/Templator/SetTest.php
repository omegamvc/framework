<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use PHPUnit\Framework\TestCase;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;

final class SetTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCanRenderSetString()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{% set $foo=\'bar\' %}');
        $this->assertEquals('<?php $foo = \'bar\'; ?>', $out);
    }

    /**
     * @return void
     */
    public function testItCanRenderSetInt()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{% set $bar=123 %}');
        $this->assertEquals('<?php $bar = 123; ?>', $out);
    }

    /**
     * @return void
     */
    public function testItCanRenderSetArray()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{% set $arr=[12, \'34\'] %}');
        $this->assertEquals('<?php $arr = [12, \'34\']; ?>', $out);
    }
}
