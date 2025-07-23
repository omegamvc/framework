<?php

/**
 * Part of Omega - Tests\Http Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Http;

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Http\HttpKernel;
use Omega\Http\Request;
use Omega\Http\Response;
use Omega\Application\Application;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function ob_get_clean;
use function ob_start;

/**
 * Unit tests for HttpKernel termination behavior.
 *
 * This test suite verifies the termination process of the HttpKernel,
 * ensuring that the termination callbacks are properly executed after
 * handling a request and response.
 *
 * @category  Omega\Tests
 * @package   Http
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(HttpKernel::class)]
#[CoversClass(Request::class)]
#[CoversClass(Response::class)]
#[CoversClass(Application::class)]
class KernelTerminateTest extends TestCase
{
    /** @var Application The application instance used for testing. */
    private Application $app;

    /** @var HttpKernel|null The HttpKernel instance (anonymous class) used during testing. */
    private ?HttpKernel $kernel;

    /**
     * Set up the test environment before each test.
     *
     * This method is called before each test method is run.
     * Override it to initialize objects, mock dependencies, or reset state.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->app = new Application('/');

        $this->app->set(
            HttpKernel::class,
            fn () => new $this->kernel($this->app)
        );

        $this->kernel = new class ($this->app) extends HttpKernel {
            /**
             * Handle the incoming request and return a simple response.
             *
             * @param Request $request
             * @return Response
             */
            public function handle(Request $request): Response
            {
                return new Response('ok');
            }

            /**
             * Return the middleware stack for this kernel.
             *
             * @param Request $request
             * @return array<int, class-string>
             */
            protected function dispatcherMiddleware(Request $request): array
            {
                return [TestKernelTerminate::class];
            }
        };
    }

    /**
     * Clean up the test environment after each test.
     *
     * This method flushes and resets the application container
     * to ensure a clean state between tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->app->flush();
        $this->kernel = null;
    }

    /**
     * Test it can terminate.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanTerminate(): void
    {
        $kernel      = $this->app->make(HttpKernel::class);
        $response    = $kernel->handle(
            $request = new Request('/test')
        );

        $this->app->registerTerminate(static function () {
            echo 'terminated.';
        });

        ob_start();
        $kernel->terminate($request, $response);
        $out = ob_get_clean();

        $this->assertEquals('/testokterminated.', $out);
    }
}
