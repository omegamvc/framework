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

use Omega\Http\Exceptions\StreamedResponseCallableException;
use Omega\Http\Request;
use Omega\Http\StreamedResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the StreamedResponse class.
 *
 * This test suite covers the creation and behavior of streamed HTTP responses,
 * including constructor usage, following headers from a Request, sending content,
 * and handling errors when the callable is invalid.
 *
 * @category  Omega\Tests
 * @package   Http
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Request::class)]
#[CoversClass(StreamedResponse::class)]
class StreamedResponseTest extends TestCase
{
    /**
     * Test it can use constructor.
     *
     * @return void
     */
    public function testItCanUseConstructor(): void
    {
        $response = new StreamedResponse(function () {
            echo 'php';
        }, 200, ['Content-Type' => 'text/plain']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/plain', $response->getHeaders()['Content-Type']);
    }

    /**
     * Test it can create stream response using request.
     *
     * @return void
     */
    public function testItCanCreateStreamResponseUsingRequest(): void
    {
        $response = new StreamedResponse(function () {
            echo 'php';
        }, 200, ['Content-Type' => 'application/json']);
        $request  = new Request('', [], [], [], [], [], ['Content-Type' => 'text/plain'], 'HEAD');
        $response->followRequest($request, ['Content-Type']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/plain', $response->getHeaders()['Content-Type']);
    }

    /**
     * Test it can send content.
     *
     * @return void
     */
    public function testItCanSendContent(): void
    {
        $called = 0;

        $response = new StreamedResponse(function () use (&$called) {
            $called++;
        });

        (fn () => $this->{'sendContent'}())->call($response);
        $this->assertEquals(1, $called);

        (fn () => $this->{'sendContent'}())->call($response);
        $this->assertEquals(1, $called);
    }

    /**
     * Test it can send content with non callable.
     *
     * @¶eturn void
     */
    public function testItCanSendContentWithNonCallable(): void
    {
        $this->expectException(StreamedResponseCallableException::class);
        $response = new StreamedResponse(null);
        (fn () => $this->{'sendContent'}())->call($response);
    }
}
