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

use ArrayAccess;
use ArrayIterator;
use Closure;
use Exception;
use IteratorAggregate;
use Omega\Collection\Collection;
use Omega\Collection\CollectionImmutable;
use Omega\Http\Upload\UploadFile;
use Omega\Macroable\MacroableTrait;
use Omega\Text\Str;
use Omega\Validator\Validator;
use ReturnTypeWillChange;
use Traversable;

use function array_merge;
use function func_num_args;
use function get_debug_type;
use function in_array;
use function is_array;
use function json_decode;
use function sprintf;
use function strcasecmp;
use function strtoupper;
use function substr;

use const JSON_BIGINT_AS_STRING;
use const JSON_THROW_ON_ERROR;

/**
 * Represents an incoming HTTP request.
 *
 * Encapsulates data from global variables like $_GET, $_POST, $_FILES, $_COOKIE, and $_SERVER,
 * providing a structured and testable way to access request-related information such as method,
 * URL, headers, query parameters, form data, JSON body, and uploaded files.
 *
 * Implements ArrayAccess and IteratorAggregate to allow convenient access to custom attributes.
 *
 * @category  Omega
 * @package   Http
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @method Validator    validate(?Closure $rule = null, ?Closure $filter = null)
 * @method UploadFile upload(array $file_name)
 *
 * @implements ArrayAccess<string, string>
 * @implements IteratorAggregate<string, string>
 *
 * @property string|null $query_1
 */
class Request implements ArrayAccess, IteratorAggregate
{
    use MacroableTrait;

    /**
     * The HTTP request method (e.g., GET, POST, PUT, DELETE).
     */
    private string $method;

    /**
     * The full URL of the incoming request.
     */
    private string $url;

    /**
     * Query parameters from the $_GET superglobal.
     *
     * @var Collection<string, string>
     */
    private Collection $query;

    /**
     * Custom request attributes (e.g., route parameters or manually added data).
     *
     * @var array<string, string|int|bool>
     */
    private array $attributes;

    /**
     * Form data from the $_POST superglobal.
     *
     * @var Collection<string, string>
     */
    private Collection $post;

    /**
     * Uploaded files from the $_FILES superglobal.
     *
     * @var array<string, array<int, string>|string>
     */
    private array $files;

    /**
     * Cookies from the $_COOKIE superglobal.
     *
     * @var array<string, string>
     */
    private array $cookies;

    /**
     * HTTP headers sent with the request.
     *
     * @var array<string, string>
     */
    private array $headers;

    /**
     * IP address of the client that made the request.
     */
    private string $remoteAddress;

    /**
     * Raw request body (e.g., JSON payloads or XML data).
     *
     * @var string|null
     */
    private ?string $rawBody;

    /**
     * Parsed JSON body content, if available.
     *
     * @var Collection<string, string>
     */
    private Collection $json;

    /**
     * Mapping of format names to their corresponding MIME types.
     * Used for content negotiation and response formatting.
     *
     * @var array<string, string[]>
     */
    protected array $formats = [
        'html'   => ['text/html', 'application/xhtml+xml'],
        'txt'    => ['text/plain'],
        'js'     => ['application/javascript', 'application/x-javascript', 'text/javascript'],
        'css'    => ['text/css'],
        'json'   => ['application/json', 'application/x-json'],
        'jsonld' => ['application/ld+json'],
        'xml'    => ['text/xml', 'application/xml', 'application/x-xml'],
        'rdf'    => ['application/rdf+xml'],
        'atom'   => ['application/atom+xml'],
        'rss'    => ['application/rss+xml'],
        'form'   => ['application/x-www-form-urlencoded', 'multipart/form-data'],
    ];

    /**
     * Creates a new HTTP request instance.
     *
     * @param string                 $url           The full request URL.
     * @param array<string, string> $query         Query parameters ($_GET).
     * @param array<string, string> $post          Post parameters ($_POST).
     * @param array<string, string> $attributes    Custom attributes (e.g. route variables).
     * @param array<string, string> $cookies       Cookie values ($_COOKIE).
     * @param array<string, string> $files         Uploaded files ($_FILES).
     * @param array<string, string> $headers       HTTP headers.
     * @param string                $method        HTTP method (GET, POST, etc.).
     * @param string                $remoteAddress Client IP address.
     * @param string|null           $rawBody       Raw body content (e.g., JSON).
     */
    public function __construct(
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
    ) {
        $this->initialize($url, $query, $post, $attributes, $cookies, $files, $headers, $method, $remoteAddress, $rawBody);
    }

    /**
     * Initializes the request instance with raw input data.
     *
     * Used internally to assign all request-related values in one step.
     *
     * @param string                 $url
     * @param array<string, string> $query
     * @param array<string, string> $post
     * @param array<string, string|int|bool> $attributes
     * @param array<string, string> $cookies
     * @param array<string, string> $files
     * @param array<string, string> $headers
     * @param string                $method
     * @param string                $remoteAddress
     * @param string|null           $rawBody
     * @return $this
     */
    public function initialize(
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
    ): self {
        $this->url             = $url;
        $this->query           = new Collection($query);
        $this->post            = new Collection($post);
        $this->attributes      = $attributes;
        $this->cookies         = $cookies;
        $this->files           = $files;
        $this->headers         = $headers;
        $this->method          = $method;
        $this->remoteAddress   = $remoteAddress;
        $this->rawBody         = $rawBody;

        return $this;
    }

    /**
     * Creates a new request object based on the current one with optional overrides.
     *
     * This is useful when you want to fork the request with different input sets,
     * e.g. for testing or programmatic manipulation.
     *
     * @param array<string, string>|null $query
     * @param array<string, string>|null $post
     * @param array<string, string|int|bool>|null $attributes
     * @param array<string, string>|null $cookies
     * @param array<string, string>|null $files
     * @param array<string, string>|null $headers
     * @return self
     */
    public function duplicate(
        ?array $query = null,
        ?array $post = null,
        ?array $attributes = null,
        ?array $cookies = null,
        ?array $files = null,
        ?array $headers = null,
    ): self {
        $duplicate = clone $this;

        if (null !== $query) {
            $duplicate->query = new Collection($query);
        }
        if (null !== $post) {
            $duplicate->post = new Collection($post);
        }
        if (null !== $attributes) {
            $duplicate->attributes = $attributes;
        }
        if (null !== $cookies) {
            $duplicate->cookies = $cookies;
        }
        if (null !== $files) {
            $duplicate->files = $files;
        }
        if (null !== $headers) {
            $duplicate->headers = $headers;
        }

        return $duplicate;
    }

    /**
     * Ensures deep cloning of internal collections.
     *
     * Prevents shared references between request clones.
     */
    public function __clone(): void
    {
        $this->query      = clone $this->query;
        $this->post       = clone $this->post;
        // cloning as array
        $this->attributes = (new Collection($this->attributes))->all();
        $this->cookies    = (new Collection($this->cookies))->all();
        $this->files      = (new Collection($this->files))->all();
        $this->headers    = (new Collection($this->headers))->all();
    }

    /**
     * Returns the full URL of the request.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Returns the immutable query parameter collection ($_GET).
     *
     * @return CollectionImmutable<string, string>
     */
    public function query(): CollectionImmutable
    {
        return $this->query->immutable();
    }

    /**
     * Retrieves one or all query parameters from $_GET.
     *
     * @param string|null $key Specific key to fetch, or null to get all.
     * @return array<string, string>|string
     */
    public function getQuery(?string $key = null): array|string
    {
        if (func_num_args() === 0) {
            return $this->query->all();
        }

        return $this->query->get($key);
    }

    /**
     * Returns the immutable post parameter collection ($_POST).
     *
     * @return CollectionImmutable<string, string>
     */
    public function post(): CollectionImmutable
    {
        return $this->post->immutable();
    }

    /**
     * Retrieves one or all post parameters from $_POST.
     *
     * @param string|null $key Specific key to fetch, or null to get all.
     * @return array<string, string>|string
     */
    public function getPost(?string $key = null): array|string
    {
        if (func_num_args() === 0) {
            return $this->post->all();
        }

        return $this->post->get($key);
    }

    /**
     * Retrieves one or all uploaded files from $_FILES.
     *
     * @param string|null $key Specific file key, or null to get all.
     * @return array<string, array<int, string>|string>|array<int, string>|string
     */
    public function getFile(?string $key = null): array|string
    {
        if (func_num_args() === 0) {
            return $this->files;
        }

        return $this->files[$key];
    }

    /**
     * Retrieves a specific cookie value by key.
     *
     * @param string $key The name of the cookie.
     * @return string|null The cookie value, or null if not set.
     */
    public function getCookie(string $key): ?string
    {
        return $this->cookies[$key] ?? null;
    }

    /**
     * Returns all cookies associated with the request.
     *
     * @return array<string, string>|null An associative array of cookie names and values.
     */
    public function getCookies(): ?array
    {
        return $this->cookies;
    }

    /**
     * Returns the HTTP method used for the request (e.g., GET, POST).
     *
     * @return string The request method in uppercase.
     */
    public function getMethod(): string
    {
        return strtoupper($this->method);
    }

    /**
     * Checks if the request method matches the given method.
     *
     * @param string $method Method name to compare (case-insensitive).
     * @return bool True if the method matches, false otherwise.
     */
    public function isMethod(string $method): bool
    {
        return strcasecmp($this->method, $method) === 0;
    }

    /**
     * Retrieves one or all request headers.
     *
     * @param string|null $header Specific header name, or null to get all.
     * @return array<string, string>|string|null Header value(s), or null if not found.
     */
    public function getHeaders(?string $header = null): array|string|null
    {
        if ($header === null) {
            return $this->headers;
        }

        return $this->headers[$header] ?? null;
    }

    /**
     * Returns all MIME types associated with a given format.
     *
     * @param string $format The format key (e.g., 'json', 'html').
     * @return string[] List of MIME types for the specified format.
     */
    public function getMimeTypes(string $format): array
    {
        return $this->formats[$format] ?? [];
    }

    /**
     * Resolves the format name from a given MIME type.
     *
     * @param string|null $mimeType The MIME type to match.
     * @return string|null The format name (e.g., 'json'), or null if not found.
     */
    public function getFormat(?string $mimeType): ?string
    {
        foreach ($this->formats as $format => $mimeTypes) {
            if (in_array($mimeType, $mimeTypes)) {
                return $format;
            }
        }

        return null;
    }

    /**
     * Detects the request format based on the 'Content-Type' header.
     *
     * @return string|null The resolved format (e.g., 'json', 'xml'), or null if unknown.
     */
    public function getRequestFormat(): ?string
    {
        $contentType = $this->getHeaders('content-type');

        return $this->getFormat($contentType);
    }

    /**
     * Checks if a specific header is present and matches the given value.
     *
     * @param string $header_key Header name.
     * @param string $header_val Expected header value.
     * @return bool True if the header exists and matches the value, false otherwise.
     */
    public function isHeader(string $header_key, string $header_val): bool
    {
        if (isset($this->headers[$header_key])) {
            return $this->headers[$header_key] === $header_val;
        }

        return false;
    }

    /**
     * Determines if the request contains the specified header.
     *
     * @param string $header_key Header name to check.
     * @return bool True if the header exists, false otherwise.
     */
    public function hasHeader(string $header_key): bool
    {
        return isset($this->headers[$header_key]);
    }

    /**
     * Checks if the current request is made over HTTPS.
     *
     * @return bool True if the request is secured (HTTPS), false otherwise.
     */
    public function isSecured(): bool
    {
        return !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off');  // http;
    }

    /**
     * Returns the IP address from which the request originated.
     *
     * @return string The client IP address.
     */
    public function getRemoteAddress(): string
    {
        return $this->remoteAddress;
    }

    /**
     * Retrieves the raw body of the request.
     *
     * @return string|null The raw body content, or null if not available.
     */
    public function getRawBody(): ?string
    {
        return $this->rawBody;
    }

    /**
     * Decodes the request body as an associative JSON array.
     *
     * @return array The decoded JSON body.
     * @throws Exception If the body is empty, not decodable, or not an array.
     */
    public function getJsonBody(): array
    {
        if ('' === $content = $this->rawBody) {
            throw new Exception('Request body is empty.');
        }

        try {
            $content = json_decode($content, true, 512, JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            throw new Exception('Could not decode request body.', $e->getCode(), $e);
        }

        if (!is_array($content)) {
            throw new Exception(sprintf('JSON content was expected to decode to an array, "%s" returned.', get_debug_type($content)));
        }

        return $content;
    }

    /**
     * Retrieves a custom request attribute by key.
     *
     * @param string $key     The attribute name.
     * @param bool|int|string $default Default value if the attribute is not set.
     * @return bool|int|string The attribute value or the default.
     */
    public function getAttribute(string $key, bool|int|string $default): bool|int|string
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Merges custom attributes into the request.
     *
     * This can be used to inject data into the request during processing.
     *
     * @param array<string, string|int|bool> $pushAttributes Attributes to add.
     * @return self
     */
    public function with(array $pushAttributes): self
    {
        $this->attributes = array_merge($this->attributes, $pushAttributes);

        return $this;
    }

    /**
     * Returns a merged array of all request data.
     *
     * Includes headers, query/post input, attributes, cookies, files, method, and raw body.
     *
     * @return array<string, mixed> The complete request data.
     * @throws Exception If there is an error processing input.
     */
    public function all(): array
    {
        /** @var Collection<string, string> $input */
        $input = $this->input();

        return array_merge(
            $this->headers,
            $input->toArray(),
            $this->attributes,
            $this->cookies,
            [
                'x-raw'     => $this->getRawBody() ?? '',
                'x-method'  => $this->getMethod(),
                'files'     => $this->files,
            ]
        );
    }

    /**
     * Wraps the full request data array in a single-item array.
     *
     * @return array<int, array<string, mixed>> An array containing the full request array.
     * @throws Exception If there is an error processing input.
     */
    public function wrap(): array
    {
        return [$this->all()];
    }

    /**
     * Checks whether the request is an AJAX request.
     *
     * @return bool True if the request was made via XMLHttpRequest.
     */
    public function isAjax(): bool
    {
        return $this->getHeaders('X-Requested-With') == 'XMLHttpRequest';
    }

    /**
     * Determines if the request expects or contains JSON.
     *
     * @return bool True if the content type is JSON-based.
     */
    public function isJson(): bool
    {
        /** @var string $contentType */
        $contentType = $this->getHeaders('content-type') ?? '';

        return Str::contains($contentType, '/json') || Str::contains($contentType, '+json');
    }

    /**
     * Returns the request JSON body as a collection.
     *
     * Parses and caches the body content if it hasn't been processed yet.
     *
     * @return Collection<string, string> The JSON body as a key-value collection.
     * @throws Exception If the body is not valid JSON or not an array.
     */
    public function json(): Collection
    {
        if (false === isset($this->json)) {
            $jsonBody = [];
            foreach ($this->getJsonBody() as $key => $value) {
                $jsonBody[(string) $key] = (string) $value;
            }
            $this->json = new Collection($jsonBody);
        }

        return $this->json;
    }

    /**
     * Retrieves the `Authorization` header from the request.
     *
     * @return string|null The authorization header value, or null if not set.
     */
    public function getAuthorization(): ?string
    {
        return $this->getHeaders('Authorization');
    }

    /**
     * Extracts the Bearer token from the `Authorization` header.
     *
     * @return string|null The bearer token, or null if not present or invalid.
     */
    public function getBearerToken(): ?string
    {
        $authorization = $this->getAuthorization();
        if (null === $authorization) {
            return null;
        }

        if (Str::startsWith($authorization, 'Bearer ')) {
            return substr($authorization, 7);
        }

        return null;
    }

    /**
     * Retrieves input from the request, combining body and query parameters.
     *
     * @template TGetDefault
     * @param string|null $key The specific input key to retrieve, or null for all.
     * @param TGetDefault $default The default value if the key is not present.
     * @return Collection<string, string>|string|TGetDefault The input value(s).
     * @throws Exception If input resolution fails.
     */
    public function input(?string $key = null, $default = null)
    {
        $input = $this->source()->add($this->query->all());
        if (null === $key) {
            return $input;
        }

        return $input->get($key, $default);
    }

    /**
     * Gets the main input source based on the request method and content type.
     *
     * Returns JSON body for JSON requests, query for GET/HEAD, and post for others.
     *
     * @return Collection<string, string> The appropriate input source.
     * @throws Exception If JSON decoding fails.
     */
    private function source(): Collection
    {
        if ($this->isJson()) {
            return $this->json();
        }

        return in_array($this->method, ['GET', 'HEAD']) ? $this->query : $this->post;
    }

    /**
     * Checks whether an input value exists for the given key.
     *
     * Used for ArrayAccess support.
     *
     * @param string $offset The input key.
     * @return bool True if the key exists.
     * @throws Exception If input resolution fails.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->source()->has($offset);
    }

    /**
     * Retrieves the value at a given input offset.
     *
     * @param string $offset The input key.
     * @return string|null The value or null if not found.
     * @throws Exception If request data can't be collected.
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): ?string
    {
        return $this->__get($offset);
    }

    /**
     * Sets an input value for the given key.
     *
     * @param string $offset The key to set.
     * @param string $value The value to set.
     * @return void
     * @throws Exception If the input source is not writable.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->source()->set($offset, $value);
    }

    /**
     * Removes an input value by its key.
     *
     * @param string $offset The key to remove.
     * @return void
     * @throws Exception If the input source is not writable.
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->source()->remove($offset);
    }

    /**
     * Retrieves a value from the full request using property-style access.
     *
     * @param string $key The key to retrieve.
     * @return string|null The value or null if not found.
     * @throws Exception If request data can't be collected.
     */
    public function __get(string $key): ?string
    {
        return $this->all()[$key] ?? null;
    }

    /**
     * Returns an iterator over the request input data.
     *
     * Enables `foreach` iteration over request values.
     *
     * @return Traversable
     * @throws Exception If input resolution fails.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->source()->all());
    }
}
