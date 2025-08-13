<?php

declare(strict_types=1);

namespace Omega\Http\Exceptions;

use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{
    /**
     * Http status code.
     */
    private int $statusCode;

    /**
     * Http Headers information.
     *
     * @var array<string, string>
     */
    private array $headers;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        int        $statusCode,
        string     $message,
        ?Throwable $previous = null,
        array      $headers = [],
        int        $code = 0,
    ) {
        $this->statusCode = $statusCode;
        $this->headers    = $headers;
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get Http Header.
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
