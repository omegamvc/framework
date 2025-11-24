<?php

declare(strict_types=1);

namespace Tests\View;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\View\Exceptions\ViewFileNotFoundException;
use Omega\View\View;

use function ob_get_clean;
use function ob_start;
use function str_replace;

use const DIRECTORY_SEPARATOR;

#[CoversClass(ViewFileNotFoundException::class)]
#[CoversClass(View::class)]
class RenderViewTest extends TestCase
{
    /**
     * Test it can render using view classes.
     *
     * @return void
     */
    public function testItCanRenderUsingViewClasses(): void
    {
        $testHtml  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'sample.html';
        $testPhp   = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'sample.php';

        ob_start();
        View::render($testHtml)->send();
        $renderHtml = ob_get_clean();

        ob_start();
        View::render($testPhp, ['contents' => ['say' => 'hay']])->send();
        $renderPhp = ob_get_clean();

        // view: view-html
        $this->assertEquals(
            "<html><head></head><body></body></html>\n",
            str_replace("\r\n", "\n", $renderHtml),
            'it must same output with template html'
        );

        // view: view-php
        $this->assertEquals(
            "<html><head></head><body><h1>hay</h1></body></html>\n",
            str_replace("\r\n", "\n", $renderPhp),
            'it must same output with template html'
        );
    }

    /**
     * Test it throw when file not found.
     *
     * @return void
     */
    public function testItThrowWhenFileNotFound(): void
    {
        $this->expectException(ViewFileNotFoundException::class);
        View::render('unknow');
    }
}
