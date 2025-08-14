<?php

declare(strict_types=1);

namespace Omega\Testing;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Omega\Http\Request;
use Omega\Http\Response;
use Omega\Application\Application;
use Omega\Http\HttpKernel;
use Omega\Container\Provider\AbstractServiceProvider;
use Omega\Support\Facades\AbstractFacade;
use Throwable;

use function array_key_exists;

class TestCase extends PhpUnitTestCase
{
    protected Application $app;

    protected HttpKernel $kernel;

    protected string $class;

    protected function tearDown(): void
    {
        $this->app->flush();
        AbstractFacade::flushInstance();
        AbstractServiceProvider::flushModule();
        unset($this->app);
        unset($this->kernel);
    }

    /**
     * @param string|array<string, string> $call call the given function using the given parameters
     * @param array<string, string> $params
     * @throws Exception
     */
    protected function json(array|string $call, array $params = []): TestJsonResponse
    {
        $data     = $this->app->call($call, $params);
        $response = new Response($data);
        if (array_key_exists('code', $data)) {
            $response->setResponseCode((int) $data['code']);
        }
        if (array_key_exists('headers', $data)) {
            $response->setHeaders($data['headers']);
        }

        return new TestJsonResponse($response);
    }

    /**
     * @param string $url
     * @param array $query
     * @param array $post
     * @param array $attributes
     * @param array $cookies
     * @param array $files
     * @param array $headers
     * @param string $method
     * @param string $remoteAddress
     * @param string|null $rawBody
     * @return TestResponse
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Throwable
     */
    protected function call(
        string $url,
        array $query = [],
        array $post = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $headers = [],
        string $method = 'GET',
        string $remoteAddress = '::1',
        ?string $rawBody = null,
    ): TestResponse {
        /** @var HttpKernel $kernel */
        $kernel  = $this->app->make(HttpKernel::class);
        $request = new Request(
            $url,
            $query,
            $post,
            $attributes,
            $cookies,
            $files,
            $headers,
            $method,
            $remoteAddress,
            $rawBody
        );
        $response = $kernel->handle($request);

        $kernel->terminate($request, $response);

        return new TestResponse($response);
    }

    /**
     * @param array<string, string> $parameter
     */
    /**
     * @param string                $url
     * @param array<string, string> $parameter
     * @return TestResponse
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Throwable
     */
    protected function get(string $url, array $parameter = []): TestResponse
    {
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        return $this->call(url: $url, query: $parameter, method: 'GET');
    }

    /**
     * @param string $url
     * @param array<string, string> $post
     * @param array<string, string> $files
     * @return TestResponse
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Throwable
     */
    protected function post(string $url, array $post, array $files =[]): TestResponse
    {
        return $this->call(url: $url, post: $post, files: $files, method: 'POST');
    }

    /**
     * @param string                $url
     * @param array<string, string> $put
     * @param array<string, string> $files
     * @return TestResponse
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Throwable
     */
    protected function put(string $url, array $put, array $files = []): TestResponse
    {
        return $this->call(url: $url, attributes: $put, files: $files, method: 'PUT');
    }

    /**
     * @param string                $url
     * @param array<string, string> $delete
     * @return TestResponse
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Throwable
     */
    protected function delete(string $url, array $delete): TestResponse
    {
        return $this->call(url: $url, post: $_POST, method: 'DELETE');
    }
}
