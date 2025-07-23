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

use Omega\Http\RedirectResponse;
use Omega\Testing\TestResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for the RedirectResponse and TestResponse classes.
 *
 * This test verifies that a RedirectResponse is correctly constructed
 * with the expected status code and location header, and that its
 * content is properly rendered and testable using the TestResponse utility.
 *
 * @category  Omega\Tests
 * @package   Http
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(RedirectResponse::class)]
#[CoversClass(TestResponse::class)]
class RedirectResponseTest extends TestCase
{
    /**
     * Test it can get response content.
     *
     * @return void
     */
    public function testItCanGetResponseContent(): void
    {
        $res      = new RedirectResponse('/login');
        $redirect = new TestResponse($res);

        $redirect->assertSee('Redirecting to /login');
        $redirect->assertStatusCode(302);

        foreach ($res->getHeaders() as $key => $value) {
            if ('Location' === $key) {
                $this->assertEquals('/login', $value);
            }
        }
    }
}
