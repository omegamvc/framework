<?php

declare(strict_types=1);

namespace Omega\Http;

use ArrayObject;
use Exception;
use InvalidArgumentException;

use function json_decode;
use function json_encode;

use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;

class JsonResponse extends Response
{
    protected string $data;

    /**
     * @see https://github.com/symfony/symfony/blob/6.4/src/Symfony/Component/HttpFoundation/JsonResponse.php
     */
    protected int $encoding_options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

    /**
     * @param array|null $data
     * @param int        $statusCode
     * @param array<string, string> $headers Header to send to client
     * @throws Exception
     */
    public function __construct(?array $data = null, int $statusCode = 200, array $headers = [])
    {
        parent::__construct('', $statusCode, $headers);
        $data ??= new ArrayObject();
        $this->setData($data);
    }

    /**
     * @param int $encodingOptions
     * @return self
     * @throws Exception
     */
    public function setEncodingOptions(int $encodingOptions): self
    {
        $this->encoding_options = $encodingOptions;
        $this->setData(json_decode($this->data));

        return $this;
    }

    /**
     * @return int
     */
    public function getEncodingOptions(): int
    {
        return $this->encoding_options;
    }

    /**
     * @param string $json
     * @return self
     */
    public function setJson(string $json): self
    {
        $this->data = $json;
        $this->prepare();

        return $this;
    }

    /**
     * @throws Exception throw error when json encode is false
     */
    public function setData(mixed $data): self
    {
        if (false === ($json = json_encode($data, $this->encoding_options))) {
            throw new InvalidArgumentException('Invalid encode data.');
        }

        $this->data = $json;
        $this->prepare();

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return json_decode($this->data, true);
    }

    protected function prepare(): void
    {
        $this->setContentType('application/json');
        $this->setContent($this->data);
    }
}
