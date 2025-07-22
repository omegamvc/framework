<?php

/**
 * Part of Omega - Http Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Http\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Class HttpException
 *
 * Represents a generic HTTP exception that carries a status code and optional headers.
 * Typically used to signal HTTP error responses like 404, 403, etc.
 *
 * @category   Omega
 * @package    Http
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class HttpException extends RuntimeException implements HttpExceptionInterface
{
    /** @var int HTTP status code (e.g., 404, 500) */
    private int $statusCode;

    /** @var array<string, string> HTTP response headers to be sent with the exception */
    private array $headers;

    /**
     * Creates a new HTTP exception instance.
     *
     * @param int                   $statusCode HTTP status code associated with the exception.
     * @param string                $message    Exception message.
     * @param Throwable|null        $previous   Optional previous exception for chaining.
     * @param array<string, string> $headers    Optional HTTP headers to send with the response.
     * @param int                   $code       Optional internal exception code.
     * @return void
     */
    public function __construct(
        int $statusCode,
        string $message,
        ?Throwable $previous = null,
        array $headers = [],
        int $code = 0,
    ) {
        $this->statusCode = $statusCode;
        $this->headers    = $headers;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the HTTP status code associated with the exception.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get HTTP headers associated with the exception.
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
