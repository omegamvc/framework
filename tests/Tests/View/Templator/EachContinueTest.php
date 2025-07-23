<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use PHPUnit\Framework\TestCase;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;

final class EachContinueTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCanRenderEachContinue()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{% foreach $numbsers as $number %}{% continue %}{% endforeach %}');
        $this->assertEquals('<?php foreach ($numbsers as $number ): ?><?php continue ; ?><?php endforeach; ?>', $out);
    }
}
