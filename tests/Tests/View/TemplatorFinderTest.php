<?php

declare(strict_types=1);

namespace Tests\View;

use PHPUnit\Framework\TestCase;
use Omega\View\Exceptions\ViewFileNotFound;
use Omega\View\TemplatorFinder;

class TemplatorFinderTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCanFindTemplatorFileLocation(): void
    {
        $loader = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';

        $view = new TemplatorFinder([$loader], ['.php']);

        $this->assertEquals($loader . DIRECTORY_SEPARATOR . 'php.php', $view->find('php'));
    }

    /**
     * @return void
     */
    public function testItCanFindTemplatorFileLocationWillThrows(): void
    {
        $loader = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';

        $view = new TemplatorFinder([$loader], ['.php']);

        $this->expectException(ViewFileNotFound::class);
        $view->find('blade');
    }

    /**
     * @return void
     */
    public function testItCanCheckFIleExist(): void
    {
        $loader = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';

        $view = new TemplatorFinder([$loader], ['.php', '.component.php']);

        $this->assertTrue($view->exists('php'));
        $this->assertTrue($view->exists('repeat'));
        $this->assertFalse($view->exists('index.blade'));
    }

    /**
     * @return void
     */
    public function testItCanFindInPath(): void
    {
        $loader = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';

        $view = new TemplatorFinder([$loader], ['.php']);

        $this->assertEquals($loader . DIRECTORY_SEPARATOR . 'php.php', (fn () => $this->{'findInPath'}('php', [$loader]))->call($view));
    }

    /**
     * @return void
     */
    public function testItCanFindInPathWillThrowException(): void
    {
        $loader = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';

        $view = new TemplatorFinder([$loader], ['.php']);

        $this->expectException(ViewFileNotFound::class);
        (fn () => $this->{'findInPath'}('blade', [$loader]))->call($view);
    }

    /**
     * @return void
     */
    public function testItCanAddPath(): void
    {
        $loader = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';

        $view = new TemplatorFinder([], ['.php']);
        $view->addPath($loader);

        $this->assertEquals($loader . DIRECTORY_SEPARATOR . 'php.php', $view->find('php'));
    }

    /**
     * @return void
     */
    public function testItCanSetPath(): void
    {
        $loader = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';

        $view  = new TemplatorFinder([], ['.php']);
        $paths = (fn () => $this->{'paths'})->call($view);
        $this->assertEquals([], $paths);
        $view->setPaths([$loader]);
        $paths = (fn () => $this->{'paths'})->call($view);
        $this->assertEquals([$loader], $paths);
    }

    /**
     * @return void
     */
    public function testItCanNotAddMultyPath(): void
    {
        $loader = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';

        $view = new TemplatorFinder([], ['.php']);
        $view->addPath($loader);
        $view->addPath($loader);
        $view->addPath($loader);

        $this->assertEquals([$loader], $view->getPaths());
    }

    /**
     * @return void
     */
    public function testItCanAddExtension(): void
    {
        $loader = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';

        $view = new TemplatorFinder([$loader]);
        $view->addExtension('.php');

        $this->assertEquals($loader . DIRECTORY_SEPARATOR . 'php.php', $view->find('php'));
    }

    /**
     * @return void
     */
    public function testItCanFlush(): void
    {
        $loader = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';

        $view = new TemplatorFinder([$loader], ['.php']);

        $view->find('php');
        $views = (fn () => $this->{'views'})->call($view);
        $this->assertCount(1, $views);
        $view->flush();
        $views = (fn () => $this->{'views'})->call($view);
        $this->assertCount(0, $views);
    }

    /**
     * @return void
     */
    public function testItCanGetPathsRegistered(): void
    {
        $loader = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';

        $view = new TemplatorFinder([$loader], ['.php']);

        $this->assertEquals([$loader], $view->getPaths());
    }

    /**
     * @return void
     */
    public function testItCanGetExtensionsRegistered(): void
    {
        $loader = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';

        $view = new TemplatorFinder([$loader], ['.php']);

        $this->assertEquals(['.php'], $view->getExtensions());
    }
}
