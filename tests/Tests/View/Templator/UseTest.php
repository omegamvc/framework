<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use PHPUnit\Framework\TestCase;
use Omega\Text\Str;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;

final class UseTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCanRenderUse()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates("'<html>{% use ('Test\Test') %}</html>");
        $match     = Str::contains($out, 'use Test\Test');
        $this->assertTrue($match);
    }

    /**
     * @return void
     */
    public function testItCanRenderUseMultyTime()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates("'<html>{% use ('Test\Test') %}{% use ('Test\Test as Test2') %}</html>");
        $match     = Str::contains($out, 'use Test\Test');
        $this->assertTrue($match);
        $match     = Str::contains($out, 'use Test\Test as Test2');
        $this->assertTrue($match);
    }
}
