<?php

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
use function in_array;
use function is_array;
use function is_numeric;
use function is_string;
use function json_encode;
use function ob_end_clean;
use function ob_end_flush;
use function ob_get_status;
use function preg_replace;
use function sprintf;
use function str_contains;

use const PHP_OUTPUT_HANDLER_CLEANABLE;
use const PHP_OUTPUT_HANDLER_FLUSHABLE;
use const PHP_OUTPUT_HANDLER_REMOVABLE;
use const PHP_SAPI;

class Response
{
    public const int HTTP_OK = 200;
    public const int HTTP_CREATED = 201;
    public const int HTTP_ACCEPTED = 202;
    public const int HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    public const int HTTP_NO_CONTENT = 204;
    public const int HTTP_MOVED_PERMANENTLY = 301;
    public const int HTTP_BAD_REQUEST = 400;
    public const int HTTP_UNAUTHORIZED = 401;
    public const int HTTP_PAYMENT_REQUIRED = 402;
    public const int HTTP_FORBIDDEN = 403;
    public const int HTTP_NOT_FOUND = 404;
    public const int HTTP_METHOD_NOT_ALLOWED= 405;

    /**
     * Status response text.
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

    /** @var string|array Http body content. */
    private string|array $content;

    /** @var int Http response code. */
    private int $responseCode;

    /** @var HeaderCollection Header array pools. */
    public HeaderCollection $headers;

    /** @var array<int, string> List header to be hide/remove to client. */
    private array $removeHeaders = [];

    private bool $removeDefaultHeaders = false;

    /** @var string Content type. */
    private string $contentType = 'text/html';

    /** @var string Http Protocol version (1.0 or 1.1). */
    private string $protocolVersion;

    /** @var int Set encoding option for encode json data. */
    protected int $encodingOption = JSON_NUMERIC_CHECK;

    /**
     * Create rosone http base on content and header.
     *
     * @param array|string $content               Content to serve to client
     * @param int                   $responseCode Response code
     * @param array<string, string> $headers      Header to send to client
     */
    public function __construct(array|string $content = '', int $responseCode = Response::HTTP_OK, array $headers = [])
    {
        $this->setContent($content);
        $this->setResponseCode($responseCode);
        $this->headers = new HeaderCollection($headers);
        $this->setProtocolVersion('1.1');
    }

    /**
     * Get raw http response include http version, header, content.
     *
     * @return string
     */
    public function __toString(): string
    {
        $responseCode   = $this->responseCode;
        $responseText   = Response::$statusTexts[$responseCode] ?? 'ok';
        $responseHeader = sprintf('HTTP/%s %s %s', $this->getProtocolVersion(), $responseCode, $responseText);

        $headerLines = (string) $this->headers;
        $content     = is_array($this->content)
            ? json_encode($this->content, $this->encodingOption)
            : $this->content;

        return
            $responseHeader . "\r\n" .
            $headerLines . "\r\n" .
            "\r\n" .
            $content;
    }

    /**
     * Send header to client from header array pool,
     * include response code.
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
        // add custom header
        foreach ($this->headers as $key => $header) {
            header($key . ':' . $header);
        }

        // remove header
        foreach ($this->removeHeaders as $header) {
            header_remove($header);
        }
    }

    /**
     * Print/echo content to client,
     * also send header to client.
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
     * Cleans or flushes output buffers up to target level.
     *
     * Resulting level can be greater than target level if a non-removable buffer has been encountered.
     *
     *
     * @param int  $targetLevel
     * @param bool $flush
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
     * Send data to client.
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
     * Send data to client with json format.
     *
     * @param string|array|null $content Content to send data
     * @return self
     */
    public function json(string|array|null $content = null): self
    {
        $this->contentType = 'application/json';

        if ($content != null) {
            $this->setContent($content);
        }

        return $this;
    }

    /**
     * Send data to client with html format.
     *
     * @param bool $minify If true html tag will be sent minify
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
     * Send data to client with plan format.
     *
     * @return self
     */
    public function plainText(): self
    {
        $this->contentType = 'text/html';

        return $this;
    }

    /**
     * Minify html content.
     *
     * @param string $content Raw html content
     * @return string
     */
    private function minify(string $content): string
    {
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
     * Its instant of exit application.
     *
     * @return void
     */
    public function close(): void
    {
        exit;
    }

    /**
     * Set Content.
     *
     * @param string|array $content Raw Content
     * @return self
     */
    public function setContent(string|array $content): self
    {
        $this->content  = $content;

        return $this;
    }

    /**
     * Set repone code (override).
     *
     * @param int $responseCode
     * @return self
     */
    public function setResponseCode(int $responseCode): self
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * Set header pools (override).
     *
     * @param array<string, string> $headers
     * @return self
     * @throws Exception
     * @todo deprecated use headers property instead
     */
    public function setHeaders(array $headers): self
    {
        $this->headers->clear();

        foreach ($headers as $headerName => $header) {
            if (is_numeric($headerName)) {
                if (!str_contains($header, ':')) {
                    continue;
                }

                $this->headers->setRaw($header);
                continue;
            }

            $this->headers->set($headerName, $header);
        }

        return $this;
    }

    /**
     * Set http protocol version.
     *
     * @param string $version
     * @return self
     */
    public function setProtocolVersion(string $version): self
    {
        $this->protocolVersion = $version;

        return $this;
    }

    /**
     * Set content type.
     *
     * @param string $contentType
     * @return self
     */
    public function setContentType(string $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Removes a specified header from the origin headers
     * and handles exceptions when sending headers to the client.
     *
     * @param string|array<int, string> $headers
     * @return self
     */
    public function removeHeader(string|array $headers): self
    {
        if (is_string($headers)) {
            $this->removeHeaders[] = $headers;

            return $this;
        }

        // @todo deprecated use `removeHeaders` instead
        $this->removeHeaders($headers);

        return $this;
    }

    /**
     * Removes a specified header from the origin headers
     * and handles exceptions when sending headers to the client.
     *
     * @param array<int, string> $headers
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
     * @param bool $removeDefaultHeader
     * @return $this
     */
    public function removeDefaultHeader(bool $removeDefaultHeader = false): self
    {
        $this->removeDefaultHeaders = $removeDefaultHeader;

        return $this;
    }

    /**
     * Add new header to headers pools.
     *
     * @param string $header
     * @param string|null $value
     * @return self
     * @throws Exception
     * @todo deprecated use headers property instead
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
     * @todo deprecated use headers property instead
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers->toArray();
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->responseCode;
    }

    /**
     * @return string|array
     */
    public function getContent(): string|array
    {
        return $this->content;
    }

    /**
     * Get http protocol version.
     *
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * Get Content http response content type.
     *
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Prepare response to send header to client.
     *
     * The response header will follow response request
     *
     * @param Request            $request    Http Web Request
     * @param array<int, string> $headerName Response header will be followed from request
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
     * Informational status code 1xx.
     */
    public function isInformational(): bool
    {
        return $this->responseCode > 99 && $this->responseCode < 201;
    }

    /**
     * Successful status code 2xx.
     */
    public function isSuccessful(): bool
    {
        return $this->responseCode > 199 && $this->responseCode < 301;
    }

    /**
     * Redirection status code 3xx.
     */
    public function isRedirection(): bool
    {
        return $this->responseCode > 299 && $this->responseCode < 401;
    }

    /**
     * Client error status code 4xx.
     *
     * @return bool
     */
    public function isClientError(): bool
    {
        return $this->responseCode > 399 && $this->responseCode < 501;
    }

    /**
     * Server error status code 5xx.
     *
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->responseCode > 499 && $this->responseCode < 601;
    }
}
