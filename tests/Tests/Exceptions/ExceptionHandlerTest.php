<?php

/**
 * Part of Omega - Tests\Exceptions Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Exceptions;

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Application\Application;
use Omega\Exceptions\ExceptionHandler;
use Omega\Http\Exceptions\HttpException;
use Omega\Http\HttpKernel;
use Omega\Http\Request;
use Omega\Http\Response;
use Omega\Support\PackageManifest;
use Omega\Text\Str;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Throwable;

use function dirname;

/**
 * Unit tests for the ExceptionHandler class.
 *
 * This test suite ensures that the ExceptionHandler is correctly handling:
 * - rendering exceptions to JSON and HTML responses
 * - distinguishing between debug and production modes
 * - reporting exceptions
 * - responding with proper HTTP codes
 * - interacting with the templating system
 *
 * @category  Omega\Tests
 * @package   Exceptions
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Application::class)]
#[CoversClass(ExceptionHandler::class)]
#[CoversClass(HttpException::class)]
#[CoversClass(HttpKernel::class)]
#[CoversClass(Request::class)]
#[CoversClass(Response::class)]
#[CoversClass(PackageManifest::class)]
#[CoversClass(Str::class)]
#[CoversClass(Templator::class)]
#[CoversClass(TemplatorFinder::class)]
class ExceptionHandlerTest extends TestCase
{
    /** @var Application The application container used for testing. */
    private Application $app;

    /** @var HttpKernel The HTTP kernel that simulates request handling. */
    private HttpKernel $kernel;

    /** @var ExceptionHandler The exception handler under test. */
    private ExceptionHandler $handler;

    /** @var string[] Captures reported exceptions for test assertions. */
    public static array $logs = [];

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
        $this->app = new Application(dirname(__DIR__));

        /**
         * Returns a mocked PackageManifest instance for testing purposes.
         *
         * @return PackageManifest
         */
        $this->app->set(PackageManifest::class, fn () => new PackageManifest(
            basePath: dirname(__DIR__) . '/fixtures/exceptions/app2/',
            applicationCachePath: dirname(__DIR__) . '/fixtures/exceptions/app2/bootstrap/cache/',
            vendorPath: '/app2/package/'
        ));

        /**
         * Returns an instance of the custom HttpKernel used for testing.
         *
         * @return HttpKernel
         */
        $this->app->set(
            HttpKernel::class,
            fn () => new $this->kernel($this->app)
        );

        /**
         * Returns the test-specific ExceptionHandler instance.
         *
         * @return ExceptionHandler
         */
        $this->app->set(
            ExceptionHandler::class,
            fn () => $this->handler
        );

        $this->kernel = new class ($this->app) extends HttpKernel {
            /**
             * Dispatches the given request.
             *
             * In this test implementation, it always throws an HttpException with status 429
             * to simulate a "Too Many Requests" scenario.
             *
             * @param Request $request The incoming HTTP request.
             * @return array An array with route handling details (never returned in this case).
             * @throws HttpException Always thrown to simulate a rate limit error.
             */
            protected function dispatcher(Request $request): array
            {
                throw new HttpException(429, 'Too Many Request');
                /**
                 * Returns a default successful response; used as a fallback.
                 *
                 * @return Response
                 */
                return [
                    'callable'   => fn () => new Response('ok', 200),
                    'parameters' => [],
                    'middleware' => [],
                ];
            }
        };

        $this->handler = new class ($this->app) extends ExceptionHandler {
            /**
             * Renders the given exception into an HTTP response.
             *
             * This test-specific implementation bypasses normal behavior to allow easier testing
             * of JSON exception responses and direct message rendering.
             *
             * @param Request   $request The incoming HTTP request.
             * @param Throwable $th      The exception to handle.
             * @return Response The rendered HTTP response.
             */
            public function render(Request $request, Throwable $th): Response
            {
                // try to bypass test for json format
                if ($request->isJson()) {
                    return $this->handleJsonResponse($th);
                }

                if ($th instanceof HttpException) {
                    return new Response($th->getMessage(), $th->getStatusCode(), $th->getHeaders());
                }

                return parent::render($request, $th);
            }

            /**
             * Reports the given exception.
             *
             * In this test implementation, the exception message is stored in a static array
             * for later assertion, instead of being logged or sent to an error tracking system.
             *
             * @param Throwable $th The exception to report.
             * @return void
             */
            public function report(Throwable $th): void
            {
                ExceptionHandlerTest::$logs[] = $th->getMessage();
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
        ExceptionHandlerTest::$logs = [];
    }

    /**
     * Test it can render exception.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Throwable
     */
    public function testItCanRenderException(): void
    {
        $kernel      = $this->app->make(HttpKernel::class);
        $response    = $kernel->handle(new Request('/test'));

        $this->assertEquals('Too Many Request', $response->getContent());
        $this->assertEquals(429, $response->getStatusCode());
    }

    /**
     * Test it can report exception.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Throwable
     */
    public function testItCanReportException(): void
    {
        $kernel      = $this->app->make(HttpKernel::class);
        $kernel->handle(new Request('/test'));

        $this->assertEquals(['Too Many Request'], ExceptionHandlerTest::$logs);
    }

    /**
     * Test it can render json.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Throwable
     */
    public function testItCanRenderJson(): void
    {
        /**
         * Register a callback to set application debug mode at boot time.
         *
         * This allows testing different behavior in debug vs. production mode.
         *
         * @return void
         */
        $this->app->bootedCallback(function () {
            $this->app->set('app.debug', false);
        });

        $kernel      = $this->app->make(HttpKernel::class);
        $response    = $kernel->handle(new Request('/test', [], [], [], [], [], [
            'content-type' => 'application/json',
        ]));

        $this->assertEquals([
            'code'     => 500,
            'messages' => [
                'message'   => 'Internal Server Error',
            ],
        ], $response->getContent());
        $this->assertEquals(429, $response->getStatusCode());
    }

    /**
     * Test it can render json for debug.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Throwable
     */
    public function testItCanRenderJsonForDebug(): void
    {
        $this->app->bootedCallback(function () {
            $this->app->set('app.debug', true);
        });

        $kernel   = $this->app->make(HttpKernel::class);
        $response = $kernel->handle(new Request('/test', [], [], [], [], [], [
            'content-type' => 'application/json',
        ]));

        $content = $response->getContent();

        $this->assertEquals('Too Many Request', $content['messages']['message']);
        $this->assertEquals(HttpException::class, $content['messages']['exception']);

        // Line assertion using reflection on the dispatcher method (anonymous class)
        $reflectedKernel = new ReflectionClass($this->kernel);
        $dispatcher      = $reflectedKernel->getMethod('dispatcher');
        $expectedLine    = $dispatcher->getStartLine() + 2; // the line with "throw"

        $this->assertEquals($expectedLine, $content['messages']['line']);
        $this->assertEquals(429, $response->getStatusCode());
    }

    /**
     * Test it can render http exception.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Throwable
     */
    public function testItCanRenderHttpException(): void
    {
        $this->app->setViewPath('/fixtures/exceptions/');
        $this->app->setViewPaths([
            '/fixtures/exceptions/',
            '/fixtures/exceptions/pages/',
        ]);

        $this->app->set(
            TemplatorFinder::class,
            fn () => new TemplatorFinder(view_paths(), ['.php', '.template.php'])
        );

        $this->app->set(
            'view.instance',
            fn (TemplatorFinder $finder) => new Templator($finder, dirname(__DIR__) . '/fixtures/exceptions/pages')
        );

        $this->app->set(
            'view.response',
            fn () => fn (string $view_path, array $portal = []): Response => new Response(
                $this->app->make(Templator::class)->render($view_path, $portal)
            )
        );

        $handler = $this->app->make(ExceptionHandler::class);

        $exception = new HttpException(429, 'Internal Error', null, []);
        $render    = (fn () => $this->{'handleHttpException'}($exception))->call($handler);

        $this->assertTrue(Str::contains($render->getContent(), '<h1>Too Many Request</h1>'));
    }
}
