<?php

/** @noinspection PhpUnreachableStatementInspection */

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

namespace Tests\Http\Exceptions;

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Http\Exceptions\HttpException;
use Omega\Http\HttpKernel;
use Omega\Http\Request;
use Omega\Http\Response;
use Omega\Application\Application;
use Omega\Exceptions\ExceptionHandler;
use Omega\Support\PackageManifest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Throwable;

use function dirname;

/**
 * Unit test for verifying HTTP exception handling in the HttpKernel pipeline.
 *
 * This test ensures that when an exception is thrown during request dispatching,
 * it is correctly caught and rendered by the custom ExceptionHandler.
 *
 * The test bootstraps a minimal application context with mocked services
 * and overrides the dispatcher logic to throw a controlled HttpException.
 * The response is then validated for expected content and status code.
 *
 * @category   Omega\Tests
 * @package    Http
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Request::class)]
#[CoversClass(Response::class)]
#[CoversClass(Application::class)]
#[CoversClass(ExceptionHandler::class)]
#[CoversClass(HttpException::class)]
#[CoversClass(HttpKernel::class)]
#[CoversClass(PackageManifest::class)]
class HttpKernelHandleExceptionTest extends TestCase
{
    /**
     * The main application container instance.
     * Used to resolve dependencies and manage services during the test lifecycle.
     *
     * @var Application
     */
    private Application $app;

    /**
     * The custom HTTP kernel instance under test.
     * Handles incoming HTTP requests and dispatches responses or exceptions.
     *
     * @var HttpKernel
     */
    private HttpKernel $kernel;

    /** @var ExceptionHandler Custom exception handler instance used to render exceptions during request handling. */
    private ExceptionHandler $handler;

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
            basePath: dirname(__DIR__, 2) . '/fixtures/http/app/',
            applicationCachePath: dirname(__DIR__, 2) . '/fixtures/http/app/bootstrap/cache/',
            vendorPath: '/app/package/'
        ));

        $this->app->set(
            HttpKernel::class,
            fn () => new $this->kernel($this->app)
        );

        $this->app->set(
            ExceptionHandler::class,
            fn () => $this->handler
        );

        $this->kernel = new class ($this->app) extends HttpKernel {
            protected function dispatcher(Request $request): array
            {
                throw new HttpException(500, 'Test Exception');

                return [
                    'callable'   => fn () => new Response('ok', 200),
                    'parameters' => [],
                    'middleware' => [],
                ];
            }
        };

        $this->handler = new class ($this->app) extends ExceptionHandler {
            public function render(Request $request, Throwable $th): Response
            {
                return new Response($th->getMessage(), 500);
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
    }

    /**
     * Test it can render exception.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanRenderException(): void
    {
        $kernel      = $this->app->make(HttpKernel::class);
        $response    = $kernel->handle(new Request('/test'));

        $this->assertEquals('Test Exception', $response->getContent());
        $this->assertEquals(500, $response->getStatusCode());
    }
}
