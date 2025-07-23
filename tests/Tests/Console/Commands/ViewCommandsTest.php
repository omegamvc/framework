<?php

/**
 * Part of Omega - Tests\Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Console\Commands;

use Omega\Config\ConfigRepository;
use Omega\Console\Commands\ViewCommand;
use Omega\Application\Application;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_put_contents;
use function md5;
use function ob_start;

/**
 * Tests the behavior of the `ViewCommand` class, which handles view caching and clearing operations
 * in the Omega framework. These tests ensure that views are correctly compiled to the cache directory
 * and that cached views can be removed with the appropriate command.
 *
 * This test also indirectly covers the application container binding logic,
 * view path and cache path configuration, and interaction with the Templator system.
 *
 * @category   Omega\Tests
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Application::class)]
#[CoversClass(ConfigRepository::class)]
#[CoversClass(Templator::class)]
#[CoversClass(TemplatorFinder::class)]
#[CoversClass(ViewCommand::class)]
class ViewCommandsTest extends TestCase
{
    /** @var Application Holds the current application instance  */
    private Application $app;

    /**
     * Set up the test environment before each test.
     *
     * Initializes the application with a custom Schedule instance
     * and binds it to the service container.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->app = new Application(dirname(__DIR__, 2));
        $cachePath = '/fixtures/console/view_cache/';
        $viewPath  = '/fixtures/console/view/';

        $this->app->setCachePath($cachePath);
        $this->app->setViewPath($viewPath);

        $this->app->set(
            TemplatorFinder::class,
            fn() => new TemplatorFinder([view_path()], ['.php', ''])
        );

        $this->app->set(
            'view.instance',
            fn(TemplatorFinder $finder) => new Templator($finder, cache_path())
        );
    }

    public function testItCanCompileFromTemplatorFiles(): void
    {
        $viewCommand = new ViewCommand(['php', 'omega', 'view:cache'], [
            'prefix' => '*.php',
        ]);
        ob_start();
        $exit = $viewCommand->cache($this->app->make(Templator::class));
        ob_end_clean();

        $this->assertEquals(0, $exit);
        $this->assertFileExists(cache_path() . md5('test.php') . '.php');

        $viewCommand = new ViewCommand(['php', 'omega', 'view:clear'], [
            'prefix' => '*.php',
        ]);
        ob_start();
        $exit = $viewCommand->clear();
        ob_end_clean();

        $this->assertEquals(0, $exit);
    }

    public function testItCanGenerateAndClearCompiledViewFile(): void
    {
        file_put_contents(cache_path() . 'test01.php', '');
        file_put_contents(cache_path() . 'test02.php', '');

        $viewCommand = new ViewCommand(['php', 'omega', 'view:clear'], [
            'prefix' => '*.php',
        ]);
        ob_start();
        $exit = $viewCommand->clear();
        ob_end_clean();

        $this->assertEquals(0, $exit);
    }
}
