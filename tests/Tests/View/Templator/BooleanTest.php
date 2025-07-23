<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use PHPUnit\Framework\TestCase;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;

final class BooleanTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCanRenderBoolean()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<input x-enable="{% bool(1 == 1) %}">');
        $this->assertEquals(
            '<input x-enable="<?= (1 == 1) ? \'true\' : \'false\' ?>">',
            $out
        );
    }
}
