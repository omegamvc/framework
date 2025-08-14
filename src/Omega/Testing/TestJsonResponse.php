<?php

declare(strict_types=1);

namespace Omega\Testing;

use ArrayAccess;
use Exception;
use PHPUnit\Framework\Assert;
use Omega\Http\Response;

use ReturnTypeWillChange;
use function array_key_exists;
use function is_array;

/**
 * @implements ArrayAccess<string, mixed>
 */
class TestJsonResponse extends TestResponse implements ArrayAccess
{
    /**
     * @var array<string, mixed>
     */
    private array $responseData;

    /**
     * @throws Exception
     */
    public function __construct(Response $response)
    {
        $this->response     = $response;
        $this->responseData = (array) $response->getContent();
        if (!is_array($response->getContent())) {
            throw new Exception('Response body is not Array.');
        }
    }

    /**
     * Set response data.
     *
     * @param array<string, mixed> $responseData
     */
    public function setResponseData(array $responseData): self
    {
        $this->responseData = $responseData;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->responseData['data'];
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->responseData);
    }

    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->responseData[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->responseData[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->responseData[$offset]);
    }

    /**
     * @param string $dataKey
     * @param mixed $value
     */
    public function assertEqual(string $dataKey, mixed $value): void
    {
        $dataGet = data_get($this->responseData, $dataKey);
        Assert::assertEquals($dataGet, $value);
    }

    public function assertTrue(string $dataKey, string $message = ''): void
    {
        $dataGet = data_get($this->responseData, $dataKey);
        Assert::assertTrue($dataGet, $message);
    }

    public function assertFalse(string $dataKey, string $message = ''): void
    {
        $dataGet = data_get($this->responseData, $dataKey);
        Assert::assertFalse($dataGet, $message);
    }

    public function assertNull(string $dataKey, string $message = ''): void
    {
        $dataGet = data_get($this->responseData, $dataKey);
        Assert::assertNull($dataGet, $message);
    }

    public function assertNotNull(string $data_key, string $message = ''): void
    {
        $data_get = data_get($this->responseData, $data_key);
        Assert::assertNotNull($data_get, $message);
    }

    public function assertEmpty(string $dataKey): void
    {
        $data_get = data_get($this->responseData, $dataKey);
        Assert::assertEmpty($this->getData());
    }

    public function assertNotEmpty(string $dataKey): void
    {
        $data_get = data_get($this->responseData, $dataKey);
        Assert::assertNotEmpty($this->getData());
    }
}
