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

use Exception;
use Omega\Http\Response;
use Omega\Testing\TestJsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Class TestJsonResponseTest
 *
 * This test class verifies the behavior and assertions of {@see TestJsonResponse},
 * which extends {@see TestResponse} to handle structured JSON responses in test environments.
 *
 * It ensures array access compatibility, value extraction, and assertion methods for verifying
 * JSON payloads such as equality, truthiness, nullability, and emptiness. These tests confirm
 * that response data can be fluently and reliably inspected in integration and feature tests.
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
#[CoversClass(TestJsonResponse::class)]
class TestJsonResponseTest extends TestCase
{
    /**
     * Test it can test response as array.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanTestResponseAsArray(): void
    {
        $response = new TestJsonResponse(new Response([
            'status' => 'ok',
            'code'  => 200,
            'data'  => [
                'test' => 'success',
            ],
            'error' => null,
        ]));
        $response['test'] = 'test';

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals('test', $response['test']);
    }

    /**
     * Test it can test response assert.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanTestResponseAssert(): void
    {
        $response = new TestJsonResponse(new Response([
            'status' => 'ok',
            'code'  => 200,
            'data'  => [
                'test' => 'success',
            ],
            'error' => null,
        ]));

        $this->assertEquals(['test' => 'success'], $response->getData());
        $this->assertEquals('ok', $response['status']);
    }

    /**
     * Test it can test response assert equal.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanTestResponseAssertEqual(): void
    {
        $response = new TestJsonResponse(new Response([
            'status' => 'ok',
            'code'  => 200,
            'data'  => [
                'test' => 'success',
            ],
            'error' => null,
        ]));

        $response->assertEqual('data.test', 'success');
    }

    /**
     * Test it can test response assert true.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanTestResponseAssertTrue(): void
    {
        $response = new TestJsonResponse(new Response([
            'status' => 'ok',
            'code'  => 200,
            'data'  => [
                'test' => true,
            ],
            'error' => null,
        ]));

        $response->assertTrue('data.test');
    }

    /**
     * Test it can test response assert false.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanTestResponseAssertFalse(): void
    {
        $response = new TestJsonResponse(new Response([
            'status' => 'ok',
            'code'  => 200,
            'data'  => [
                'test' => false,
            ],
            'error' => null,
        ]));

        $response->assertFalse('data.test');
    }

    /**
     * Test it can test response assert null.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanTestResponseAssertNull(): void
    {
        $response = new TestJsonResponse(new Response([
            'status' => 'ok',
            'code'  => 200,
            'data'  => [
                'test' => false,
            ],
            'error' => null,
        ]));

        $response->assertNull('error');
    }

    /**
     * Test it can test response assert not null.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanTestResponseAssertNotNull(): void
    {
        $response = new TestJsonResponse(new Response([
            'status' => 'ok',
            'code'  => 200,
            'data'  => [
                'test' => false,
            ],
            'error' => [
                'test' => 'some error',
            ],
        ]));

        $response->assertNotNull('error');
    }

    /**
     * Test it can test response assert empty.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanTestResponseAssertEmpty(): void
    {
        $response = new TestJsonResponse(new Response([
            'status' => 'ok',
            'code'  => 200,
            'data'  => [],
            'error' => null,
        ]));

        $response->assertEmpty('error');
    }

    /**
     * Test it can test response assert not empty.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanTestResponseAssertNotEmpty(): void
    {
        $response = new TestJsonResponse(new Response([
            'status' => 'ok',
            'code'  => 200,
            'data'  => [
                'test' => false,
            ],
            'error' => null,
        ]));

        $response->assertNotEmpty('error');
    }
}
