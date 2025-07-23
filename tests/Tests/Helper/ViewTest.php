<?php

/**
 * Part of Omega - Tests\Helper Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Helper;

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Application\Application;
use Omega\Http\Response;
use Omega\Text\Str;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function dirname;

/**
 * Unit test for the view helper and rendering system.
 *
 * This test verifies the behavior of the `view()` helper function when resolving
 * dependencies from the application container and rendering a response.
 *
 * It ensures that:
 * - View-related services such as `Templator`, `TemplatorFinder`, and response closures
 *   can be registered dynamically in the container.
 * - The view helper correctly uses these services to generate a `Response` instance.
 * - The rendered content and status code match the expected output.
 *
 * The test operates on fixture templates located in the `fixtures/helper/view` directory
 * and uses a mock cache path for compiled templates.
 *
 * @category  Omega\Tests
 * @package   Helper
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Application::class)]
#[CoversClass(Response::class)]
#[CoversClass(Str::class)]
#[CoversClass(Templator::class)]
#[CoversClass(TemplatorFinder::class)]
class ViewTest extends TestCase
{
    /**
     * Test it can get response from container.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanGetResponseFromContainer(): void
    {
        $app = new Application(dirname(__DIR__));

        $app->set(
            TemplatorFinder::class,
            fn () => new TemplatorFinder([dirname(__DIR__) . '/fixtures/helper/view'], ['.php'])
        );

        $app->set(
            'view.instance',
            fn (TemplatorFinder $finder) => new Templator($finder, dirname(__DIR__) . '/fixtures/helper/cache')
        );

        $app->set(
            'view.response',
            fn () => fn (string $view_path, array $portal = []): Response => new Response(
                $app->make(Templator::class)->render($view_path, $portal)
            )
        );

        $view = view('test', [], ['status' => 500]);
        $this->assertEquals(500, $view->getStatusCode());
        $this->assertTrue(
            Str::contains($view->getContent(), 'omegamvc')
        );

        $app->flush();
    }
}
