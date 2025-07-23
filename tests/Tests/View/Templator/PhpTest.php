<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use PHPUnit\Framework\TestCase;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;

final class PhpTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCanRenderParenData()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body>{% php %} echo \'taylor\'; {% endphp %}</body></html>');
        $this->assertEquals('<html><head></head><body><?php  echo \'taylor\';  ?></body></html>', $out);
    }
}
