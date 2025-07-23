<?php

/**
 * Part of Omega - Tests\Testing Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Testing;

use Omega\Http\Response;
use Omega\Testing\TestResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Class TestResponseTest
 *
 * This test class validates the behavior of the {@see TestResponse} utility class,
 * which wraps a standard {@see Response} object and provides additional assertions
 * for use in feature and integration tests.
 *
 * It verifies content retrieval, string presence assertions, and HTTP status code checks,
 * ensuring that responses returned by the application under test meet expected criteria.
 *
 * @category  Omega\Tests
 * @package   Testing
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Response::class)]
#[CoversClass(TestResponse::class)]
class TestResponseTest extends TestCase
{
    /**
     * Test it can test response assert.
     *
     * @return void
     */
    public function testItCanTestResponseAssert(): void
    {
        $response = new TestResponse(new Response('test', 200, []));

        $this->assertEquals('test', $response->getContent());
        $response->assertSee('test');
        $response->assertStatusCode(200);
    }
}
