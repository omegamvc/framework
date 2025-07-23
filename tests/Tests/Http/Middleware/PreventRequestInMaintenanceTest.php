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

namespace Tests\Http\Middleware;

use Omega\Config\ConfigRepository;
use Omega\Http\Exceptions\HttpException;
use Omega\Http\Middleware\MaintenanceMiddleware;
use Omega\Http\Request;
use Omega\Http\Response;
use Omega\Application\Application;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for the MaintenanceMiddleware class.
 *
 * This test class verifies the behavior of the maintenance middleware
 * under different maintenance scenarios. It ensures that:
 * - Requests are allowed when maintenance is disabled.
 * - Requests are redirected when maintenance requires redirection.
 * - Requests return a rendered response with retry headers when configured.
 * - Requests throw an exception when specified.
 *
 * The tests cover the interaction between the middleware and the
 * application configuration, request/response handling, and HTTP exceptions.
 *
 * @category   Omega\Tests
 * @package    Http
 * @subpackage Middleware
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(ConfigRepository::class)]
#[CoversClass(HttpException::class)]
#[CoversClass(MaintenanceMiddleware::class)]
#[CoversClass(Response::class)]
#[CoversClass(Request::class)]
#[CoversClass(Application::class)]
class PreventRequestInMaintenanceTest extends TestCase
{
    /**
     * Test it can prevent request during maintenance.
     *
     * @return void
     */
    public function testItCanPreventRequestDuringMaintenance(): void
    {
        $app        = new Application(__DIR__);
        $app->loadConfig(new ConfigRepository($app->defaultConfigs()));
        $middleware = new MaintenanceMiddleware($app);
        $response   = new Response('test');
        $handle     = $middleware->handle(new Request('/'), fn (Request $request) => $response);

        $this->assertEquals($handle, $response);
    }

    /**
     * Test it can redirect request during maintenance.
     *
     * @return void
     */
    public function testItCanRedirectRequestDuringMaintenance(): void
    {
        $app        = new Application(dirname(__DIR__, 2));
        $app->setStoragePath(DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'storage2' . DIRECTORY_SEPARATOR);
        $middleware = new MaintenanceMiddleware($app);
        $response   = new Response('test');
        $handle     = $middleware->handle(new Request('/'), fn (Request $request) => $response);

        $this->assertEquals('/test', $handle->headers->get('Location'));
    }

    /**
     * Test it can render and retry request during maintenance.
     *
     * @return void
     */
    public function testItCanRenderAndRetryRequestDuringMaintenance(): void
    {
        $app        = new Application(dirname(__DIR__, 2));
        $app->setStoragePath(DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'storage3' . DIRECTORY_SEPARATOR);

        $middleware = new MaintenanceMiddleware($app);
        $response   = new Response('test');
        $handle     = $middleware->handle(new Request('/'), fn (Request $request) => $response);

        $this->assertEquals('<h1>Test</h1>', $handle->getContent());
        $this->assertEquals(15, $handle->headers->get('Retry-After'));
        $this->assertEquals(503, $handle->getStatusCode());
    }

    /**
     * Test it can throw request during maintenance.
     *
     * @return void
     */
    public function testItCanThrowRequestDuringMaintenance(): void
    {
        $app        = new Application(dirname(__DIR__, 2));
        $app->setStoragePath(DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR);

        $middleware = new MaintenanceMiddleware($app);
        $response   = new Response('test');

        $this->expectException(HttpException::class);
        $middleware->handle(new Request('/'), fn (Request $request) => $response);
    }
}
