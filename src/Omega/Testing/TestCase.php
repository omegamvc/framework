<?php

/**
 * Part of Omega - Testing Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Testing;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Omega\Application\Application;
use Omega\Container\Provider\AbstractServiceProvider;
use Omega\Http\HttpKernel;
use Omega\Http\Request;
use Omega\Http\Response;
use Omega\Support\Facades\Facade;
use Omega\Testing\Traits\ResponseStatusTrait;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Throwable;

use function array_key_exists;

/**
 * Base test case class for functional HTTP testing within the application.
 *
 * Provides utility methods for making HTTP requests and handling responses in a
 * test environment, including support for JSON and form-based requests.
 *
 * This class ensures application teardown and environment reset after each test,
 * and includes helper traits for working with response status.
 *
 * @category  Omega
 * @package   Testing
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class TestCase extends BaseTestCase
{
    use ResponseStatusTrait;

    /** @var Application|null The application instance used during testing. */
    protected ?Application $app;

    /** @var HttpKernel The HTTP kernel used to handle requests in the test context. */
    protected HttpKernel $kernel;

    /** @var string The class name under test. */
    protected string $class;

    /**
     * Clean up the test environment after each test execution.
     *
     * Flushes the application container, facade instances, and loaded service providers
     * to ensure test isolation and avoid side effects.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->app->flush();
        Facade::flushInstance();
        AbstractServiceProvider::flushModule();
        unset($this->app);
        unset($this->kernel);
    }

    /**
     * Perform a JSON request using the given route or callback.
     *
     * @param string|array<string, string> $call    The callback or controller action to invoke.
     * @param array<string, string>        $params  The parameters to pass to the callable.
     * @return TestJsonResponse                     The test JSON response wrapper.
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
     * Perform a custom HTTP request against the application kernel.
     *
     * @param string                $url            The request URL.
     * @param array<string, string> $query          GET query parameters.
     * @param array<string, string> $post           POST form parameters.
     * @param array<string, string> $attributes     Route or middleware attributes.
     * @param array<string, string> $cookies        Cookie data.
     * @param array<string, string> $files          Uploaded file data.
     * @param array<string, string> $headers        HTTP headers.
     * @param string                $method         HTTP method (GET, POST, PUT, DELETE).
     * @param string                $remoteAddress  Client IP address.
     * @param string|null           $rawBody        Optional raw body for JSON or XML.
     * @return TestResponse                         The response wrapper for testing.
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
        $kernel   = $this->app->make(HttpKernel::class);
        $request  = new Request(
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
     * Perform a GET request with optional query parameters.
     *
     * @param string                $url       The request URL.
     * @param array<string, string> $parameter Query parameters.
     * @return TestResponse                   The test response.
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Throwable
     */
    protected function get(string $url, array $parameter = []): TestResponse
    {
        return $this->call(url: $url, query: $parameter, method: 'GET');
    }

    /**
     * Perform a POST request with form data and optional file uploads.
     *
     * @param string                $url   The request URL.
     * @param array<string, string> $post  POST form data.
     * @param array<string, string> $files File upload data.
     * @return TestResponse               The test response.
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Throwable
     */
    protected function post(string $url, array $post, array $files = []): TestResponse
    {
        return $this->call(url: $url, post: $post, files: $files, method: 'POST');
    }

    /**
     * Perform a PUT request with form data and optional file uploads.
     *
     * @param string                $url   The request URL.
     * @param array<string, string> $put   PUT data.
     * @param array<string, string> $files File upload data.
     * @return TestResponse               The test response.
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Throwable
     */
    protected function put(string $url, array $put, array $files = []): TestResponse
    {
        return $this->call(url: $url, attributes: $put, files: $files, method: 'PUT');
    }

    /**
     * Perform a DELETE request with optional data.
     *
     * @param string                $url    The request URL.
     * @param array<string, string> $delete DELETE data (usually passed via POST).
     * @return TestResponse                The test response.
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Throwable
     */
    protected function delete(string $url, array $delete): TestResponse
    {
        return $this->call(url: $url, post: $_POST, method: 'DELETE');
    }
}
