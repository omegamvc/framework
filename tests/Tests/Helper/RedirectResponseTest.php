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

use Exception;
use Omega\Router\Router;
use Omega\Testing\TestResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Unit tests for redirect responses using the routing system.
 *
 * This test suite verifies the behavior of the redirect helpers and the Router
 * when generating HTTP redirect responses. It ensures that:
 *
 * - Redirects are generated with the correct status code (302).
 * - Named routes correctly interpolate route parameters.
 * - Plain URL redirects work as expected.
 * - Errors are properly thrown when route patterns are not matched.
 *
 * These tests rely on the `Router` and `redirect_route`/`redirect` helpers,
 * and assert expected content and status codes using `TestResponse`.
 *
 * @category  Omega\Tests
 * @package   Helper
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(TestResponse::class)]
#[CoversClass(Router::class)]
class RedirectResponseTest extends TestCase
{
    /**
     * Test it can redirect to correct url.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRedirectToCorrectUrl(): void
    {
        Router::get('/test/(:any)', fn ($test) => $test)->name('test');
        $redirect = redirect_route('test', ['ok']);
        $response = new TestResponse($redirect);
        $response->assertStatusCode(302);
        $response->assertSee('Redirecting to /test/ok');

        Router::reset();
    }

    /**
     * Test it can redirect to correct url with plan url.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRedirectToCorrectUrlWithPlanUrl(): void
    {
        Router::get('/test', fn ($test) => $test)->name('test');
        $redirect = redirect_route('test');
        $response = new TestResponse($redirect);
        $response->assertStatusCode(302);
        $response->assertSee('Redirecting to /test');

        Router::reset();
    }

    /**
     * Test it throw error when pattern no exists.
     *
     * @return void
     */
    public function testItThrowErrorWhenPatternNotExist(): void
    {
        Router::get('/test/(:test)', fn ($test) => $test)->name('test');
        $message = '';
        try {
            redirect_route('test', ['test']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
        }
        $this->assertEquals('parameter not matches with any pattern.', $message);

        Router::reset();
    }

    /**
     * Test it can redirect using url given.
     *
     * @return void
     */
    public function testItCanRedirectUsingUlrGiven()
    {
        $redirect = redirect('/test');
        $response = new TestResponse($redirect);
        $response->assertStatusCode(302);
        $response->assertSee('Redirecting to /test');
    }
}
