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

use Omega\Http\Request;
use Omega\Http\Response;
use Omega\Text\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function json_decode;
use function ob_get_clean;
use function ob_start;

/**
 * Unit tests for the Response class.
 *
 * This test suite covers rendering of HTML and JSON responses,
 * content modification, header management, status code handling,
 * and protocol version management in HTTP responses.
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
#[CoversClass(Response::class)]
#[CoversClass(Str::class)]
class ResponseTest extends TestCase
{
    /**
     * @var Response
     */
    private Response $responseHtml;
    /**
     * @var Response
     */
    private Response $responseJson;

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
        $html = '<html lang="en"><head></head><body></body></html>';
        $json = [
            'status'  => 'ok',
            'code'    => 200,
            'data'    => null,
        ];

        $this->responseHtml = new Response($html, 200, []);
        $this->responseJson = new Response($json, 200, []);
    }

    /**
     * Test it render html response.
     *
     * @retunr void
     */
    public function testItRenderHtmlResponse(): void
    {
        ob_start();
        $this->responseHtml->html()->send();
        $html = ob_get_clean();

        $this->assertEquals(
            '<html lang="en"><head></head><body></body></html>',
            $html
        );
    }

    /**
     * test it can render json response.
     *
     * @return void
     */
    public function testItRenderJsonResponse(): void
    {
        ob_start();
        $this->responseJson->json()->send();
        $json = ob_get_clean();

        $this->assertJson($json);
        $this->assertEquals(
            [
                'status'  => 'ok',
                'code'    => 200,
                'data'    => null,
            ],
            json_decode($json, true)
        );
    }

    /**
     * Test it can be edited content.
     *
     * @return void
     */
    public function testItCanBeEditedContent(): void
    {
        $this->responseHtml->setContent('edited');

        ob_start();
        $this->responseHtml->html()->send();
        $html = ob_get_clean();

        $this->assertEquals(
            'edited',
            $html
        );
    }

    /**
     * Test it can set header using construct header.
     *
     * @return void
     */
    public function testItCanSetHeaderUsingConstructHeader(): void
    {
        $res = new Response('content', 200, ['test' => 'test']);

        $get_header = $res->getHeaders()['test'];

        $this->assertEquals('test', $get_header);
    }

    /**
     * Test it can set header using set header.
     *
     * @return void
     */
    public function testItCanSetHeaderUsingSetHeaders(): void
    {
        $res = new Response('content');
        $res->setHeaders(['test' => 'test']);

        $get_header = $res->getHeaders()['test'];

        $this->assertEquals('test', $get_header);
    }

    /**
     * Test it can set header using header.
     *
     * @return void
     */
    public function testItCanSetHeaderUsingHeader(): void
    {
        $res = new Response('content');
        $res->header('test', 'test');

        $get_header = $res->getHeaders()['test'];

        $this->assertEquals('test', $get_header);
    }

    /**
     * Test it can set header using header and sanitizer header.
     *
     * @return void
     */
    public function testItCanSetHeaderUsingHeaderAndSanitizerHeader(): void
    {
        $res = new Response('content');
        $res->header('test : test:ok');

        $get_header = $res->getHeaders()['test'];

        $this->assertEquals('test:ok', $get_header);
    }

    /**
     * Test it can set header using follow request.
     *
     * @return void
     */
    public function testItCanSetHeaderUsingFollowRequest(): void
    {
        $req = new Request('test', [], [], [], [], [], ['test' => 'test']);
        $res = new Response('content');

        $res->followRequest($req, ['test']);
        $get_header = $res->getHeaders()['test'];

        $this->assertEquals('test', $get_header);
    }

    /**
     * Test it can get response status code.
     *
     * @return void
     */
    public function testItCanGetResponseStatusCode(): void
    {
        $res = new Response('content', 200);

        $this->assertEquals(200, $res->getStatusCode());
    }

    /**
     * Test it can get response content.
     *
     * @return void
     */
    public function testItCanGetResponseContent(): void
    {
        $res = new Response('content', 200);

        $this->assertEquals('content', $res->getContent());
    }

    /**
     * Test it can get typed of response cade.
     *
     * @return void
     */
    public function testItCanGetTypeOfResponseCode(): void
    {
        $res = new Response('content', rand(100, 199));
        $this->assertTrue($res->isInformational());

        $res = new Response('content', rand(200, 299));
        $this->assertTrue($res->isSuccessful());

        $res = new Response('content', rand(300, 399));
        $this->assertTrue($res->isRedirection());

        $res = new Response('content', rand(400, 499));
        $this->assertTrue($res->isClientError());

        $res = new Response('content', rand(500, 599));
        $this->assertTrue($res->isServerError());
    }

    /**
     * Test it can change protocol version.
     *
     * @return void
     */
    public function testItCanChangeProtocolVersion(): void
    {
        $res = new Response('content');
        $res->setProtocolVersion('1.0');

        $this->assertTrue(Str::contains((string) $res, '1.0'), 'Test protocol version');
    }
}
