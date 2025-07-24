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

use ArrayAccess;
use Exception;
use Omega\Http\Response;
use PHPUnit\Framework\Assert;
use ReturnTypeWillChange;

use function array_key_exists;
use function is_array;
use function Omega\Collection\data_get;

/**
 * TestJsonResponse provides a structured way to interact with JSON-based HTTP responses during tests.
 *
 * Extends the base TestResponse class and implements ArrayAccess to allow array-like access to JSON data.
 * Includes a variety of assertion helpers to test the contents of the JSON response payload.
 *
 * @category  Omega
 * @package   Testing
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @implements ArrayAccess<string, mixed>
 */
class TestJsonResponse extends TestResponse implements ArrayAccess
{
    /**
     * Parsed response data from the JSON response body.
     *
     * @var array<string, mixed>
     */
    private array $responseData;

    /**
     * Initialize the TestJsonResponse instance from a raw Response.
     *
     * @param Response $response The original response object to wrap.
     * @throws Exception If the response content is not a valid array.
     */
    public function __construct(Response $response)
    {
        $this->response      = $response;
        $this->responseData  = (array) $response->getContent();
        if (!is_array($response->getContent())) {
            throw new Exception('Response body is not Array.');
        }

        parent::__construct($response);
    }

    /**
     * Manually override the parsed JSON response data.
     *
     * @param array<string, mixed> $responseData The new response data.
     * @return self Returns the current instance for method chaining.
     */
    public function setResponseData(array $responseData): self
    {
        $this->responseData = $responseData;

        return $this;
    }

    /**
     * Get the value of the "data" key from the JSON response.
     *
     * @return mixed The value of the "data" key.
     */
    public function getData(): mixed
    {
        return $this->responseData['data'];
    }

    /**
     * Determine if a key exists in the response data.
     *
     * @param mixed $offset The key to check.
     * @return bool True if the key exists, false otherwise.
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->responseData);
    }

    /**
     * Get a value from the response data by key.
     *
     * @param mixed $offset The key to retrieve.
     * @return mixed The corresponding value.
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->responseData[$offset];
    }

    /**
     * Set a value in the response data.
     *
     * @param mixed $offset The key to set.
     * @param mixed $value  The value to assign.
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->responseData[$offset] = $value;
    }

    /**
     * Unset a value in the response data.
     *
     * @param mixed $offset The key to unset.
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->responseData[$offset]);
    }

    /**
     * Assert that the value at the specified key equals the given value.
     *
     * @param string $dataKey The data key to access.
     * @param mixed  $value   The expected value.
     * @return void
     */
    public function assertEqual(string $dataKey, mixed $value): void
    {
        $dataGet = data_get($this->responseData, $dataKey);
        Assert::assertEquals($dataGet, $value);
    }

    /**
     * Assert that the value at the specified key is true.
     *
     * @param string $dataKey The data key to access.
     * @param string $message Optional failure message.
     * @return void
     */
    public function assertTrue(string $dataKey, string $message = ''): void
    {
        $dataGet = data_get($this->responseData, $dataKey);
        Assert::assertTrue($dataGet, $message);
    }

    /**
     * Assert that the value at the specified key is false.
     *
     * @param string $dataKey The data key to access.
     * @param string $message Optional failure message.
     * @return void
     */
    public function assertFalse(string $dataKey, string $message = ''): void
    {
        $dataGet = data_get($this->responseData, $dataKey);
        Assert::assertFalse($dataGet, $message);
    }

    /**
     * Assert that the value at the specified key is null.
     *
     * @param string $dataKey The data key to access.
     * @param string $message Optional failure message.
     * @return void
     */
    public function assertNull(string $dataKey, string $message = ''): void
    {
        $dataGet = data_get($this->responseData, $dataKey);
        Assert::assertNull($dataGet, $message);
    }

    /**
     * Assert that the value at the specified key is not null.
     *
     * @param string $dataKey The data key to access.
     * @param string $message Optional failure message.
     * @return void
     */
    public function assertNotNull(string $dataKey, string $message = ''): void
    {
        $dataGet = data_get($this->responseData, $dataKey);
        Assert::assertNotNull($dataGet, $message);
    }

    /**
     * Assert that the data under the given key is empty.
     *
     * @param string $dataKey The data key to access.
     * @return void
     */
    public function assertEmpty(string $dataKey): void
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $dataGet = data_get($this->responseData, $dataKey);
        Assert::assertEmpty($this->getData());
    }

    /**
     * Assert that the data under the given key is not empty.
     *
     * @param string $dataKey The data key to access.
     * @return void
     */
    public function assertNotEmpty(string $dataKey): void
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $dataGet = data_get($this->responseData, $dataKey);
        Assert::assertNotEmpty($this->getData());
    }
}
