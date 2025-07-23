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

use Closure;
use DI\DependencyException;
use DI\NotFoundException;
use Omega\Http\HttpKernel;
use Omega\Http\Request;
use Omega\Http\Response;
use Omega\Application\Application;
use Omega\Support\PackageManifest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Throwable;

use function dirname;

/**
 * Test suite for the HttpKernel class.
 *
 * This class verifies the core functionality of the HTTP kernel,
 * including middleware dispatching, request handling, and application bootstrapping.
 *
 * The tests simulate middleware chains with anonymous classes to check
 * response modifications and status codes.
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
#[CoversClass(PackageManifest::class)]
class HttpKernelTest extends TestCase
{
    /**
     * @var Application The application container instance used in tests.
     */
    private Application $app;

    /**
     * @var HttpKernel|null The HTTP kernel instance or anonymous subclass used for testing.
     */
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

        // overwrite PackageManifest has been set in Application before.
        $this->app->set(PackageManifest::class, fn () => new PackageManifest(
            basePath: dirname(__DIR__) . '/fixtures/http/app2/',
            applicationCachePath: dirname(__DIR__) . '/fixtures/http/app2/bootstrap/cache/',
            vendorPath: '/fixtures/http/app2/package/'
        ));

        $this->app->set(
            HttpKernel::class,
            fn () => new $this->kernel($this->app)
        );

        $this->kernel = new class ($this->app) extends HttpKernel {
            protected function dispatcher(Request $request): array
            {
                return [
                    'callable'   => fn () => new Response('ok', 200),
                    'parameters' => [],
                    'middleware' => [
                        new class {
                            /**
                             * Passes the request to the next middleware without modification.
                             *
                             * @param Request $request The current HTTP request.
                             * @param Closure $next The next middleware in the chain.
                             * @return Response The response returned by the next middleware.
                             */
                            public function handle(Request $request, Closure $next): Response
                            {
                                return $next($request);
                            }
                        },
                        new class {
                            /**
                             * Returns a redirect response with HTTP status 303.
                             *
                             * @param Request $request The current HTTP request.
                             * @param Closure $next The next middleware in the chain.
                             * @return Response A redirect response with status 303.
                             * @noinspection PhpUnusedParameterInspection
                             */
                            public function handle(Request $request, Closure $next): Response
                            {
                                return new Response('redirect', 303);
                            }
                        },
                        new class {
                            /**
                             * Returns the response from the next middleware, or a forbidden response if none.
                             *
                             * @param Request $request The current HTTP request.
                             * @param Closure $next The next middleware in the chain.
                             * @return Response The next middleware's response or a 403 forbidden response.
                             */
                            public function handle(Request $request, Closure $next): Response
                            {
                                if ($response = $next($request)) {
                                    return $response;
                                }

                                return new Response('forbidden', 403);
                            }
                        },
                    ],
                ];
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
     * Test it can redirect by middleware.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException|
     * @throws Throwable
     */
    public function testItCanRedirectByMiddleware(): void
    {
        $response = $this->app->make(HttpKernel::class);
        $test    = $response->handle(
            new Request('test')
        );

        $this->assertEquals(
            'HTTP/1.1 303 ok' . "\r\n" .
            "\r\n" .
            "\r\n" .
            'redirect',
            $test->__toString()
        );
    }

    /**
     * Test it can bootstrap.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanBootstrap(): void
    {
        $this->assertFalse($this->app->isBootstrapped());
        $this->app->make(HttpKernel::class)->bootstrap();
        $this->assertTrue($this->app->isBootstrapped());
    }
}
