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

namespace Omega\Http;

use Exception;

use function array_merge;
use function count;
use function fastcgi_finish_request;
use function flush;
use function function_exists;
use function header;
use function header_remove;
use function headers_sent;
use function in_array;
use function is_array;
use function is_numeric;
use function is_string;
use function json_encode;
use function litespeed_finish_request;
use function ob_end_clean;
use function ob_end_flush;
use function ob_get_status;
use function preg_replace;
use function sprintf;
use function str_contains;

use const JSON_NUMERIC_CHECK;
use const PHP_OUTPUT_HANDLER_CLEANABLE;
use const PHP_OUTPUT_HANDLER_FLUSHABLE;
use const PHP_OUTPUT_HANDLER_REMOVABLE;
use const PHP_SAPI;

/**
 * Represents an HTTP response to be sent to the client.
 *
 * This class handles the HTTP status code, headers, body content, and content type.
 * It also supports various response formats (HTML, JSON, plain text), output buffering,
 * and integration with specific web server functions such as FastCGI or LiteSpeed.
 *
 * @category  Omega
 * @package   Http
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class Response
{
    /**
     * Common HTTP status code constants.
     *
     * These constants represent standard HTTP status codes used in responses.
     */
    public const int HTTP_OK                            = 200;
    public const int HTTP_CREATED                       = 201;
    public const int HTTP_ACCEPTED                      = 202;
    public const int HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    public const int HTTP_NO_CONTENT                    = 204;
    public const int HTTP_MOVED_PERMANENTLY             = 301;
    public const int HTTP_BAD_REQUEST                   = 400;
    public const int HTTP_UNAUTHORIZED                  = 401;
    public const int HTTP_PAYMENT_REQUIRED              = 402;
    public const int HTTP_FORBIDDEN                     = 403;
    public const int HTTP_NOT_FOUND                     = 404;
    public const int HTTP_METHOD_NOT_ALLOWED            = 405;

    /**
     * Mapping of HTTP status codes to their standard reason phrases.
     *
     * Used to generate human-readable status messages in responses.
     * This array helps format the HTTP status line sent to the client,
     * for example: "HTTP/1.1 404 Not Found".
     *
     * @var array<int, string>
     */
    public static array $statusTexts = [
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        301 => 'Moved Permanently',
        304 => 'Not Modified',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
    ];

    /**
     * The content to be sent in the HTTP response body.
     *
     * Can be a raw string (e.g., HTML or plain text) or an array (typically for JSON responses).
     *
     * @var string|array
     */
    private string|array $content;

    /**
     * The HTTP status code for the response (e.g., 200, 404).
     */
    private int $responseCode;

    /**
     * Collection of HTTP headers to be sent to the client.
     */
    public HeaderCollection $headers;

    /**
     * List of header names that should be removed before sending the response.
     *
     * @var array<int, string>
     */
    private array $removeHeaders = [];

    /**
     * Indicates whether all default headers should be removed before sending the response.
     */
    private bool $removeDefaultHeaders = false;

    /**
     * The MIME type of the response content (e.g., 'text/html', 'application/json').
     */
    private string $contentType = 'text/html';

    /**
     * The HTTP protocol version to use in the response (typically '1.0' or '1.1').
     */
    private string $protocolVersion;

    /**
     * The JSON encoding option used when the content is an array.
     */
    protected int $encodingOption = JSON_NUMERIC_CHECK;

    /**
     * Constructs a new HTTP response with the specified content, status code, and headers.
     *
     * @param array|string          $content      The body content to be sent to the client.
     * @param int                   $responseCode The HTTP status code.
     * @param array<string, string> $headers      The headers to be sent along with the response.
     */
    public function __construct(array|string $content = '', int $responseCode = Response::HTTP_OK, array $headers = [])
    {
        $this->setContent($content);
        $this->setResponseCode($responseCode);
        $this->headers = new HeaderCollection($headers);
        $this->setProtocolVersion('1.1');
    }

    /**
     * Converts the full response (status line, headers, body) to a string.
     *
     * Useful for debugging or logging purposes.
     *
     * @return string The complete HTTP response as a string.
     */
    public function __toString()
    {
        $responseCode   = $this->responseCode;
        $responseText   = Response::$statusTexts[$responseCode] ?? 'ok';
        $responseHeader = sprintf('HTTP/%s %s %s', $this->getProtocolVersion(), $responseCode, $responseText);

        $headerLines  = (string) $this->headers;
        $content      = is_array($this->content)
            ? json_encode($this->content, $this->encodingOption)
            : $this->content;

        return
            $responseHeader . "\r\n" .
            $headerLines . "\r\n" .
            "\r\n" .
            $content;
    }

    /**
     * Sends HTTP headers to the client, including the response code and all configured headers.
     *
     * Automatically sets the `Content-Type` header and removes headers if configured.
     *
     * @return void
     */
    private function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        // remove default header
        if ($this->removeDefaultHeaders) {
            header_remove();
        }

        // header response code
        $responseCode     = $this->responseCode;
        $responseText     = Response::$statusTexts[$responseCode] ?? 'unknown status';
        $responseTemplate = sprintf('HTTP/1.1 %s %s', $responseCode, $responseText);
        header($responseTemplate);

        // header
        $this->headers->set('Content-Type', $this->contentType);
        // add costume header
        foreach ($this->headers as $key => $header) {
            header($key . ':' . $header);
        }

        // remove header
        foreach ($this->removeHeaders as $header) {
            header_remove($header);
        }
    }

    /**
     * Outputs the response body content to the client.
     *
     * If the content is an array, it is automatically JSON-encoded.
     *
     * @return void
     */
    protected function sendContent(): void
    {
        echo is_array($this->content)
            ? json_encode($this->content, $this->encodingOption)
            : $this->content;
    }

    /**
     * Cleans or flushes output buffers up to the specified target level.
     *
     * Ensures proper output handling by removing or flushing nested output buffers.
     *
     * @param int  $targetLevel The desired buffer nesting level to retain.
     * @param bool $flush       Whether to flush (true) or clean (false) the buffers.
     *
     * @return void
     */
    public static function closeOutputBuffers(int $targetLevel, bool $flush): void
    {
        $status = ob_get_status(true);
        $level  = count($status);
        $flags  = PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE);

        while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }

    /**
     * Sends the entire HTTP response (headers and content) to the client.
     *
     * Utilizes `fastcgi_finish_request()` or `litespeed_finish_request()` if available,
     * otherwise flushes output buffers manually.
     *
     * @return self
     */
    public function send(): self
    {
        $this->sendHeaders();
        $this->sendContent();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();

            return $this;
        }

        if (function_exists('litespeed_finish_request')) {
            litespeed_finish_request();

            return $this;
        }

        if (!in_array(PHP_SAPI, ['cli', 'phpdbg'], true)) {
            static::closeOutputBuffers(0, true);
            flush();

            return $this;
        }

        return $this;
    }

    /**
     * Sets the response content type to `application/json` and sends the given content, if provided.
     *
     * @param array|string|null $content Optional content to include in the JSON response.
     *
     * @return self
     */
    public function json(array|string|null $content = null): self
    {
        $this->contentType = 'application/json';

        if ($content != null) {
            $this->setContent($content);
        }

        return $this;
    }

    /**
     * Sets the response content type to `text/html` and optionally minifies the HTML content.
     *
     * @param bool $minify Whether to minify the HTML content before sending it.
     *
     * @return self
     */
    public function html(bool $minify = false): self
    {
        $this->contentType = 'text/html';

        if (!is_array($this->content) && $minify) {
            /** @var string $stringContent */
            $stringContent = $this->content;
            $stringContent =  $this->minify($stringContent);

            $this->setContent($stringContent);
        }

        return $this;
    }

    /**
     * Sets the response content type to plain HTML.
     *
     * @return self
     */
    public function plainText(): self
    {
        $this->contentType = 'text/html';

        return $this;
    }

    /**
     * Minifies the given HTML content by removing unnecessary whitespace and comments.
     *
     * @param string $content The raw HTML content.
     *
     * @return string The minified HTML content.
     */
    private function minify(string $content): string
    {
        /** @noinspection PhpRegExpRedundantModifierInspection */
        $search = [
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/', // Remove HTML comments
        ];

        $replace = [
            '>',
            '<',
            '\\1',
            '',
        ];

        return preg_replace($search, $replace, $content) ?? $content;
    }

    /**
     * Immediately terminates the script execution.
     *
     * Use this method to end the response lifecycle after sending data to the client.
     *
     * @return void
     */
    public function close(): void
    {
        exit;
    }

    /**
     * Sets the HTTP response body content.
     *
     * Accepts either a string or an array (which will be JSON-encoded if applicable).
     *
     * @param string|array $content The content to be sent in the response body.
     * @return self
     */
    public function setContent(array|string $content): self
    {
        $this->content  = $content;

        return $this;
    }

    /**
     * Sets the HTTP status code for the response.
     *
     * @param int $responseCode The HTTP response code (e.g. 200, 404, etc.).
     * @return self
     */
    public function setResponseCode(int $responseCode): self
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * Replaces the current headers with the given header array.
     *
     * @deprecated Use the `headers` property directly instead.
     *
     * @param array<string, string> $headers Associative array of header names and values.
     * @return self
     * @throws Exception
     */
    public function setHeaders(array $headers): self
    {
        $this->headers->clear();

        foreach ($headers as $header_name => $header) {
            if (is_numeric($header_name)) {
                if (!str_contains($header, ':')) {
                    continue;
                }

                $this->headers->setRaw($header);
                continue;
            }

            $this->headers->set($header_name, $header);
        }

        return $this;
    }

    /**
     * Sets the HTTP protocol version for the response.
     *
     * Typically '1.0' or '1.1'.
     *
     * @param string $version HTTP protocol version string.
     * @return self
     */
    public function setProtocolVersion(string $version): self
    {
        $this->protocolVersion = $version;

        return $this;
    }

    /**
     * Sets the Content-Type header value for the response.
     *
     * @param string $contentType MIME type to be used (e.g., 'text/html', 'application/json').
     * @return self
     */
    public function setContentType(string $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Schedules one or more headers to be removed from the final response.
     *
     * If a string is provided, removes a single header. If an array is provided,
     * it delegates to `removeHeaders()`.
     *
     * @param string|array<int, string> $headers Header(s) to be removed.
     * @return self
     */
    public function removeHeader(string|array $headers): self
    {
        if (is_string($headers)) {
            $this->removeHeaders[] = $headers;

            return $this;
        }

        // @deprecated use `removeHeaders` instead
        $this->removeHeaders($headers);

        return $this;
    }

    /**
     * Replaces the list of headers to be removed from the response.
     *
     * @param array<int, string> $headers List of header names to remove.
     * @return self
     */
    public function removeHeaders(array $headers): self
    {
        $this->removeHeaders = [];
        foreach ($headers as $header) {
            $this->removeHeaders[] = $header;
        }

        return $this;
    }

    /**
     * Enables or disables the removal of all default headers.
     *
     * If true, `header_remove()` will be called before sending the response.
     *
     * @param bool $removeDefaultHeader Whether to remove all default headers.
     * @return self
     */
    public function removeDefaultHeader(bool $removeDefaultHeader = false): self
    {
        $this->removeDefaultHeaders = $removeDefaultHeader;

        return $this;
    }

    /**
     * Adds a new header to the response.
     *
     * @deprecated Use the `headers` property directly instead.
     *
     * @param string $header The header name or full raw header line.
     * @param string|null $value The header value (if $header is a key).
     * @return self
     * @throws Exception
     */
    public function header(string $header, ?string $value = null): self
    {
        if (null === $value) {
            $this->headers->setRaw($header);

            return $this;
        }

        $this->headers->set($header, $value);

        return $this;
    }

    /**
     * Get entry header.
     *
     * @deprecated use headers property instead
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers->toArray();
    }

    /**
     * Returns the current HTTP status code of the response.
     *
     * @return int HTTP status code (e.g. 200, 404).
     */
    public function getStatusCode(): int
    {
        return $this->responseCode;
    }

    /**
     * Returns the content of the response body.
     *
     * Can be a string or an array (which may be JSON-encoded when sent).
     *
     * @return string|array Response content.
     */
    public function getContent(): array|string
    {
        return $this->content;
    }

    /**
     * Retrieves the HTTP protocol version used in the response.
     *
     * Typically '1.0' or '1.1'.
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * Retrieves the MIME type specified in the Content-Type header.
     *
     * @return string Content type of the response.
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Copies specific headers from the incoming request to the response.
     *
     * Useful for maintaining header consistency (e.g., cache control, content type).
     *
     * @param Request            $request     The incoming HTTP request object.
     * @param array<int, string> $headerName  List of additional header names to copy.
     * @return self
     */
    public function followRequest(Request $request, array $headerName = []): self
    {
        $followRule = array_merge($headerName, [
            'cache-control',
            'content-type',
        ]);

        // header based on the Request
        foreach ($followRule as $rule) {
            if ($request->hasHeader($rule)) {
                $this->headers->set($rule, $request->getHeaders($rule));
            }
        }

        return $this;
    }

    /**
     * Checks if the response status code is informational (1xx).
     *
     * @return bool True if status is between 100 and 199.
     */
    public function isInformational(): bool
    {
        return $this->responseCode > 99 && $this->responseCode < 201;
    }

    /**
     * Checks if the response status code indicates success (2xx).
     *
     * @return bool True if status is between 200 and 299.
     */
    public function isSuccessful(): bool
    {
        return $this->responseCode > 199 && $this->responseCode < 301;
    }

    /**
     * Checks if the response status code indicates redirection (3xx).
     *
     * @return bool True if status is between 300 and 399.
     */
    public function isRedirection(): bool
    {
        return $this->responseCode > 299 && $this->responseCode < 401;
    }

    /**
     * Checks if the response status code indicates a client error (4xx).
     *
     * @return bool True if status is between 400 and 499.
     */
    public function isClientError(): bool
    {
        return $this->responseCode > 399 && $this->responseCode < 501;
    }

    /**
     * Checks if the response status code indicates a server error (5xx).
     *
     * @return bool True if status is between 500 and 599.
     */
    public function isServerError(): bool
    {
        return $this->responseCode > 499 && $this->responseCode < 601;
    }
}
