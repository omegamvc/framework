<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\Text\Str;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;

#[CoversClass(Str::class)]
#[CoversClass(Templator::class)]
#[CoversClass(TemplatorFinder::class)]
final class UseTest extends TestCase
{
    /**
     * Test it can render use.
     *
     * @return void
     * @throws Exception
     */
    public function testitCanRenderUse(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates("'<html>{% use ('Test\Test') %}</html>");
        $match     = Str::contains($out, 'use Test\Test');
        $this->assertTrue($match);
    }

    /**
     * Test it can render multi time.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderUseMultiTime(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates("'<html>{% use ('Test\Test') %}{% use ('Test\Test as Test2') %}</html>");
        $match     = Str::contains($out, 'use Test\Test');
        $this->assertTrue($match);
        $match     = Str::contains($out, 'use Test\Test as Test2');
        $this->assertTrue($match);
    }
}
