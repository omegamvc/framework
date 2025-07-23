<?php

declare(strict_types=1);

namespace Tests\View;

use PHPUnit\Framework\TestCase;
use Omega\View\Exceptions\ViewFileNotFound;
use Omega\View\View;

class RenderViewTest extends TestCase
{
    /** @return void */
    public function testItCanRenderUsingViewClasses(): void
    {
        $test_html  = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'sample.html';
        $test_php   = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'sample.php';

        ob_start();
        View::render($test_html)->send();
        $render_html = ob_get_clean();

        ob_start();
        View::render($test_php, ['contents' => ['say' => 'hay']])->send();
        $render_php = ob_get_clean();

        // view: view-html
        $this->assertEquals(
            "<html><head></head><body></body></html>\n",
            str_replace("\r\n", "\n", $render_html),
            'it must same output with template html'
        );

        // view: view-php
        $this->assertEquals(
            "<html><head></head><body><h1>hay</h1></body></html>\n",
            str_replace("\r\n", "\n", $render_php),
            'it must same output with template html'
        );
    }

    /** @return void */
    public function testItThrowWhenFileNotFound()
    {
        $this->expectException(ViewFileNotFound::class);
        View::render('unknow');
    }
}
