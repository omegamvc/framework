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
use Exception;
use Omega\Http\Request;
use Omega\Http\Upload\UploadFile;
use Omega\Validator\Rule\FilterPool;
use Omega\Validator\Rule\ValidPool;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Throwable;

use function dirname;

use const DIRECTORY_SEPARATOR;

/**
 * Unit test for the Request class and related HTTP components.
 *
 * This class covers extensive scenarios to test the behavior of the Request object,
 * including its ability to manage GET/POST data, headers, files, cookies, JSON payloads,
 * AJAX detection, validation, macros, upload handling, cloning, and format detection.
 *
 * The tests ensure that the Request class integrates correctly with the Validator,
 * FilterPool, and UploadFile classes and behaves consistently across different HTTP methods.
 *
 * @category  Omega\Tests
 * @package   Http
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(UploadFile::class)]
#[CoversClass(Request::class)]
#[CoversClass(FilterPool::class)]
#[CoversClass(ValidPool::class)]
#[CoversClass(Validator::class)]
class RequestTest extends TestCase
{
    /** @var Request Request instance used to simulate a standard GET request. */
    private Request $request;

    /** @var Request Request instance used to simulate a POST request with files. */
    private Request $requestPost;

    /** @var Request Request instance used to simulate a PUT request with a JSON body. */
    private Request $requestPut;

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
        $this->request = new Request(
            'http://localhost/',
            ['query_1'   => 'query'],
            ['post_1'    => 'post'],
            ['custom'    => 'custom'],
            ['cookies'   => 'cookies'],
            [
                'file_1' => [
                    'name'      => 'file_name',
                    'type'      => 'text',
                    'tmp_name'  => 'tmp_name',
                    'error'     => 0,
                    'size'      => 0,
                ],
            ],
            ['header_1'  => 'header', 'header_2' => 123, 'foo' => 'bar'],
            'GET',
            '127:0:0:1',
            '{"response":"ok"}'
        );

        $this->requestPost = new Request(
            'http://localhost/',
            ['query_1'   => 'query'],
            ['post_1'    => 'post'],
            ['custom'    => 'custom'],
            ['cookies'   => 'cookies'],
            [
                'file_1' => [
                    'name'      => 'file_name',
                    'type'      => 'text',
                    'tmp_name'  => 'tmp_name',
                    'error'     => 0,
                    'size'      => 0,
                ],
                'file_2' => [
                    'name'      => 'test123.txt',
                    'type'      => 'file',
                    'tmp_name'  => dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'test123.tmp',
                    'error'     => 0,
                    'size'      => 1,
                ],
            ],
            ['header_1'  => 'header', 'header_2' => 123, 'foo' => 'bar'],
            'POST',
            '127:0:0:1',
            '{"response":"ok"}'
        );

        $this->requestPut = new Request('test.test', [], [], [], [], [], [
            'content-type' => 'app/json',
        ], '', '', '{"response":"ok"}');
    }

    /**
     * Test has same url.
     *
     * @return void
     */
    public function testHasSameUrl(): void
    {
        $this->assertEquals('http://localhost/', $this->request->getUrl());
    }

    /**
     * Test has same query.
     *
     * @return void
     */
    public function testHasSameQuery(): void
    {
        $this->assertEquals('query', $this->request->getQuery('query_1'));
        $this->assertEquals('query', $this->request->query()->get('query_1'));
    }

    /**
     * Test has same post.
     *
     * @return void
     */
    public function testHasSamePost(): void
    {
        $this->assertEquals('post', $this->request->getPost('post_1'));
        $this->assertEquals('post', $this->request->post()->get('post_1'));
    }

    /**
     * Test has same cookie.
     *
     * @return void
     */
    public function testHasSameCookies(): void
    {
        $this->assertEquals('cookies', $this->request->getCookie('cookies'));
    }

    /**
     * Test has same file.
     *
     * @return void
     */
    public function testHasSameFile(): void
    {
        $file = $this->request->getFile('file_1');
        $this->assertEquals(
            'file_name',
            $file['name']
        );
        $this->assertEquals(
            'text',
            $file['type']
        );
        $this->assertEquals(
            'tmp_name',
            $file['tmp_name']
        );
        $this->assertEquals(
            0,
            $file['error']
        );
        $this->assertEquals(
            0,
            $file['size']
        );
    }

    /**
     * Test has same header.
     *
     * @return void
     */
    public function testHasSameHeader(): void
    {
        $this->assertEquals('header', $this->request->getHeaders('header_1'));
    }

    /**
     * Test has same method.
     *
     * @return void
     */
    public function testHasSameMethod(): void
    {
        $this->assertEquals('GET', $this->request->getMethod());
    }

    /**
     * Test has same ip.
     *
     * @return void
     */
    public function testHasSameIp(): void
    {
        $this->assertEquals('127:0:0:1', $this->request->getRemoteAddress());
    }

    /**
     * Test has same body.
     *
     * @return void
     */
    public function testHasSameBody(): void
    {
        $this->assertEquals('{"response":"ok"}', $this->request->getRawBody());
    }

    /**
     * Test has same body json.
     *
     * @return void
     * @throws Exception
     */
    public function testHasSameBodyJson()
    {
        $this->assertEquals(
            ['response' => 'ok'],
            $this->request->getJsonBody()
        );
    }

    /**
     * Test it not secure request.
     *
     * @return void
     */
    public function testItNotSecureRequest(): void
    {
        $this->assertFalse($this->request->isSecured());
    }

    /**
     * Test has header.
     *
     * @return void
     */
    public function testHasHeader(): void
    {
        $this->assertTrue($this->request->hasHeader('header_2'));
    }

    /**
     * Test is header contains.
     *
     * @return void
     */
    public function testIsHeaderContains(): void
    {
        $this->assertTrue($this->request->isHeader('foo', 'bar'));
    }

    /**
     * Test it can get all property.
     *
     * @return void
     */
    public function testItCanGetAllProperty(): void
    {
        $this->assertEquals($this->request->all(), [
            'header_1'          => 'header',
            'header_2'          => 123,
            'foo'               => 'bar',
            'query_1'           => 'query',
            'custom'            => 'custom',
            'x-raw'             => '{"response":"ok"}',
            'x-method'          => 'GET',
            'cookies'           => 'cookies',
            'files'             => [
                'file_1' => [
                    'name'      => 'file_name',
                    'type'      => 'text',
                    'tmp_name'  => 'tmp_name',
                    'error'     => 0,
                    'size'      => 0,
                ],
            ],
        ]);
    }

    /**
     * Test it can throw error when body empty.
     *
     * @return void
     */
    public function testItCanThrowErrorWhenBodyEmpty(): void
    {
        $request = new Request('test.test', [], [], [], [], [], ['content-type' => 'app/json'], 'PUT', '::1', '');

        try {
            $request->all();
        } catch (Throwable $th) {
            $this->assertEquals('Request body is empty.', $th->getMessage());
        }
    }

    /**
     * Test it can throw error when body cant decode.
     *
     * @return void
     */
    public function testItCanThrowErrorWhenBodyCantDecode(): void
    {
        $request = new Request('test.test', [], [], [], [], [], ['content-type' => 'app/json'], 'PUT', '::1', 'nobody');

        try {
            $request->all();
        } catch (Throwable $th) {
            $this->assertEquals('Could not decode request body.', $th->getMessage());
        }
    }

    /**
     * Test it can access as array get.
     *
     * @return void
     */
    public function testItCanAccessAsArrayGet(): void
    {
        $this->assertEquals('query', $this->request['query_1']);
        $this->assertEquals(null, $this->request['query_x']);
    }

    /**
     * Test it can access array has.
     *
     * @return void
     */
    public function testItCanAccessAsArrayHas(): void
    {
        $this->assertTrue(isset($this->request['query_1']));
        $this->assertFalse(isset($this->request['query_x']));
    }

    /**
     * Tets it can access using getter.
     *
     * @return void
     */
    public function testItCanAccessUsingGetter(): void
    {
        $this->assertEquals('query', $this->request->query_1);
    }

    /**
     * Test it can detect ajax request.
     *
     * @return void
     */
    public function testItCanDetectAjaxRequest(): void
    {
        $req = new Request('test.test', [], [], [], [], [], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertTrue($req->isAjax());
    }

    /**
     * Test ot can get item from attribute.
     *
     * @return void
     */
    public function testItCanGetItemFromAttribute(): void
    {
        $this->assertEquals('custom', $this->request->getAttribute('custom', 'fixed'));
        $this->assertEquals('fixed', $this->request->getAttribute('fixed', 'fixed'));
    }

    /**
     * Test it can use foreach request.
     *
     * @return void
     */
    public function testItCanUseForeachRequest(): void
    {
        foreach ($this->request as $key => $value) {
            $this->assertEquals($this->request[$key], $value);
        }
    }

    /**
     * Test it can detect request json request.
     *
     * @return void
     */
    public function testItCanDetectRequestJsonRequest(): void
    {
        $this->assertFalse($this->request->isJson());
        $this->assertTrue($this->requestPut->isJson());
    }

    /**
     * Test it can return body if request come from json request.
     *
     * @return void
     */
    public function testItCanReturnBodyIfRequestComeFromJsonRequest(): void
    {
        $this->assertEquals('ok', $this->requestPut->json()->get('response', 'bad'));
        $this->assertEquals('ok', $this->requestPut->all()['response']);
        $this->assertEquals('ok', $this->requestPut['response']);
    }

    /**
     * Test it can get all property if method post.
     *
     * @return void
     */
    public function testItCanGetAllPropertyIfMethodPost(): void
    {
        $this->assertEquals($this->requestPost->all(), [
            'header_1'          => 'header',
            'header_2'          => 123,
            'foo'               => 'bar',
            'query_1'           => 'query',
            'post_1'            => 'post',
            'custom'            => 'custom',
            'x-raw'             => '{"response":"ok"}',
            'x-method'          => 'POST',
            'cookies'           => 'cookies',
            'files'             => [
                'file_1' => [
                    'name'      => 'file_name',
                    'type'      => 'text',
                    'tmp_name'  => 'tmp_name',
                    'error'     => 0,
                    'size'      => 0,
                ],
                'file_2' => [
                    'name'      => 'test123.txt',
                    'type'      => 'file',
                    'tmp_name'  => dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'test123.tmp',
                    'error'     => 0,
                    'size'      => 1,
                ],
            ],
        ]);
    }

    /**
     * Test it can use validator macro.
     *
     * @return void
     */
    public function testItCanUseValidateMacro(): void
    {
        Request::macro(
            'validate',
            fn (?Closure $rule = null, ?Closure $filter = null) => Validator::make($this->{'all'}(), $rule, $filter)
        );

        // get
        $v = $this->request->validate();
        $v->field('query_1')->required();
        $this->assertTrue($v->isValid());

        // post
        $v = $this->requestPost->validate();
        $v->field('query_1')->required();
        $v->field('post_1')->required();
        $this->assertTrue($v->isValid());

        // file
        $v = $this->requestPost->validate();
        $v->field('query_1')->required();
        $v->field('post_1')->required();
        $v->field('files.file_1')->required();
        $this->assertTrue($v->isValid());

        // put
        $v = $this->requestPut->validate();
        $v->field('response')->required();
        $this->assertTrue($v->isValid());

        // get (filter)
        $v = $this->request->validate(
            fn (ValidPool $vr) => $vr('query_1')->required(),
            fn (FilterPool $fr) => $fr('query_1')->upper_case()
        );
        $this->assertTrue($v->isValid());
        $this->assertEquals('QUERY', $v->filters->get('query_1'));
    }

    /**
     * Test it can use upload macro.
     *
     * @return void
     */
    public function testItCanUseUploadMacro(): void
    {
        Request::macro(
            'upload',
            function ($file_name) {
                $files = $this->{'getFile'}();

                return (new UploadFile($files[$file_name]))->markTest(true);
            }
        );

        $upload = $this->requestPost->upload('file_2');
        $upload
            ->setFileName('success')
            ->setFileTypes(['txt', 'md'])
            ->setFolderLocation(dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR)
            ->setMaxFileSize(91)
            ->setMimeTypes(['file'])
        ;

        $upload->upload();

        $upload->delete(dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'success.txt');

        $this->assertTrue($upload->success());
    }

    /**
     * Test it can modified request.
     *
     * @return void
     */
    public function testItCanModifiedRequest(): void
    {
        $request  = new Request('test.test', ['query' => 'old'], [], [], [], [], ['content-type' => 'app/json'], 'PUT', '::1', '');
        $request2 = $request->duplicate(['query' => 'new']);

        $this->assertEquals('old', $request->getQuery('query'));
        $this->assertEquals('new', $request2->getQuery('query'));
    }

    /**
     * Test it can get mime type.
     *
     * @return void
     */
    public function testItCanGetMimeType(): void
    {
        $request  = new Request('test.test', ['query' => 'old'], [], [], [], [], ['content-type' => 'app/json'], 'PUT', '::1', '');

        $mimetypes = $request->getMimeTypes('html');
        $this->assertEquals(['text/html', 'application/xhtml+xml'], $mimetypes);

        $mimetypes = $request->getMimeTypes('php');
        $this->assertEquals([], $mimetypes, 'php format is not exists');
    }

    /**
     * Test it can get format.
     *
     * @return void
     */
    public function testItCanGetFormat(): void
    {
        $request  = new Request('test.test', ['query' => 'old'], [], [], [], [], ['content-type' => 'app/json'], 'PUT', '::1', '');

        $format = $request->getFormat('text/html');
        $this->assertEquals('html', $format);

        $format = $request->getFormat('text/php');
        $this->assertNull($format, 'php format not exist');
    }

    /**
     * Test it can get request format.
     *
     * @return void
     */
    public function testItCanGetRequestFormat(): void
    {
        $request  = new Request('test.test', ['query' => 'old'], [], [], [], [], ['content-type' => 'application/json'], 'PUT', '::1', '');

        $this->assertEquals('json', $request->getRequestFormat());
    }

    /**
     * Test it can  not get request format.
     *
     * @return void
     */
    public function testItCanNotGetRequestFormat(): void
    {
        $request  = new Request('test.test', ['query' => 'old'], [], [], [], [], [], 'PUT', '::1', '');

        $this->assertNull($request->getRequestFormat());
    }

    /**
     * Test it can get header authorization.
     *
     * @return void
     */
    public function testItCanGetHeaderAuthorization(): void
    {
        $request = new Request('test.test', headers: ['Authorization' => '123']);

        $this->assertEquals('123', $request->getAuthorization());
    }

    /**
     * Test it can get header bearer authorization.
     *
     * @return void
     */
    public function testItCanGetHeaderBearerAuthorization(): void
    {
        $request = new Request('test.test', headers: ['Authorization' => 'Bearer 123']);

        $this->assertEquals('123', $request->getBearerToken());
    }
}
