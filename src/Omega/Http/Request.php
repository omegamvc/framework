<?php

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
 * @method Validator    validate(?Closure $rule = null, ?Closure $filter = null)
 * @method UploadFile upload(array $fileName)
 *
 * @implements ArrayAccess<string, string>
 * @implements IteratorAggregate<string, string>
 */
class Request implements ArrayAccess, IteratorAggregate
{
    use MacroableTrait;

    /**
     * Request method.
     */
    private string $method;

    /**
     * Request url.
     */
    private string $url;

    /**
     * Request query ($_GET).
     *
     * @var Collection<string, string>
     */
    private Collection $query;

    /**
     * Custom request information.
     *
     * @var array<string, string|int|bool>
     */
    private array $attributes;

    /**
     * Request post ($_POST).
     *
     * @var Collection<string, string>
     */
    private Collection $post;

    /**
     * Request file ($_FILE).
     *
     * @var array<string, array<int, string>|string>
     */
    private array $files;

    /**
     * Request cookies ($_COOKIES).
     *
     * @var array<string, string>
     */
    private array $cookies;

    /**
     * Request header.
     *
     * @var array<string, string>
     */
    private array $headers;

    /**
     * Request remote address (IP).
     */
    private string $remoteAddress;

    /**
     * Request Body content.
     *
     * @var string|null
     */
    private ?string $rawBody;

    /**
     * Json body rendered.
     *
     * @var Collection<string, string>
     */
    private Collection $json;

    /**
     * Initialize mime format.
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
     * @param array<string, string> $query
     * @param array<string, string> $post
     * @param array<string, string> $attributes
     * @param array<string, string> $cookies
     * @param array<string, string> $files
     * @param array<string, string> $headers
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
        $this->initialize(
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
    }

    /**
     * Initial request.
     *
     * @param string                $url
     * @param array<string, string> $query
     * @param array<string, string> $post
     * @param array<string, string> $attributes
     * @param array<string, string> $cookies
     * @param array<string, string> $files
     * @param array<string, string> $headers
     * @param string                $method
     * @param string                $remoteAddress
     * @param string|null           $rawBody
     * @return self
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
     * Initial request.
     *
     * @param array<string, string>|null $query
     * @param array<string, string>|null $post
     * @param array<string, string>|null $attributes
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

    public function __clone(): void
    {
        $this->query      = clone $this->query;
        $this->post       = clone $this->post;
        // cloning as array
        $this->attributes = new Collection($this->attributes)->all();
        $this->cookies    = new Collection($this->cookies)->all();
        $this->files      = new Collection($this->files)->all();
        $this->headers    = new Collection($this->headers)->all();
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get query ($_GET).
     *
     * @return CollectionImmutable<string, string>
     */
    public function query(): CollectionImmutable
    {
        return $this->query->immutable();
    }

    /**
     * Get Post/s ($_GET).
     *
     * @param string|null $key
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
     * Get post ($_POST).
     *
     * @return CollectionImmutable<string, string>
     */
    public function post(): CollectionImmutable
    {
        return $this->post->immutable();
    }

    /**
     * Get Post/s ($_POST).
     *
     * @param string|null $key
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
     * Get file/s ($_FILE).
     *
     * @param string|null $key
     * @return array<string, array<int, string>|string>|array<int, string>|string
     */
    public function getFile(?string $key = null): array|string
    {
        if (func_num_args() === 0) {
            return $this->files;
        }

        return $this->files[$key];
    }

    public function getCookie(string $key): ?string
    {
        return $this->cookies[$key] ?? null;
    }

    /**
     * Get cookies.
     *
     * @return array<string, string>|null
     */
    public function getCookies(): ?array
    {
        return $this->cookies;
    }

    public function getMethod(): string
    {
        return strtoupper($this->method);
    }

    public function isMethod(string $method): bool
    {
        return strcasecmp($this->method, $method) === 0;
    }

    /**
     * Get header/s.
     *
     * @param string|null $header
     * @return array<string, string>|string|null get header/s
     */
    public function getHeaders(?string $header = null): array|string|null
    {
        if ($header === null) {
            return $this->headers;
        }

        return $this->headers[$header] ?? null;
    }

    /**
     * Gets the mime types associated with the format.
     *
     * @param string $format
     * @return string[]
     */
    public function getMimeTypes(string $format): array
    {
        return $this->formats[$format] ?? [];
    }

    /**
     * Gets format using mimetype.
     *
     * @param string|null $mimeType
     * @return string|null
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
     * Gets format type from request header.
     *
     * @return string|null
     */
    public function getRequestFormat(): ?string
    {
        $content_type = $this->getHeaders('content-type');

        return $this->getFormat($content_type);
    }

    /**
     * @param string $headerKey
     * @param string $headerVal
     * @return bool
     */
    public function isHeader(string $headerKey, string $headerVal): bool
    {
        if (isset($this->headers[$headerKey])) {
            return $this->headers[$headerKey] === $headerVal;
        }

        return false;
    }

    /**
     * @param string $header_key
     * @return bool
     */
    public function hasHeader(string $header_key): bool
    {
        return isset($this->headers[$header_key]);
    }

    /**
     * @return bool
     */
    public function isSecured(): bool
    {
        return !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off');  // http;
    }

    /**
     * @return string
     */
    public function getRemoteAddress(): string
    {
        return $this->remoteAddress;
    }

    /**
     * @return string|null
     */
    public function getRawBody(): ?string
    {
        return $this->rawBody;
    }

    /**
     * Get Json array.
     *
     * @return array
     * @throws Exception
     */
    public function getJsonBody(): array
    {
        if ('' === $content = $this->rawBody) {
            throw new Exception(
                'Request body is empty.'
            );
        }

        try {
            $content = json_decode($content, true, 512, JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            throw new Exception(
                'Could not decode request body.',
                $e->getCode(),
                $e
            );
        }

        if (!is_array($content)) {
            throw new Exception(
                sprintf(
                    'JSON content was expected to decode to an array, "%s" returned.',
                    get_debug_type($content)
                )
            );
        }

        return $content;
    }

    /**
     * Get attribute.
     *
     * @param string          $key
     * @param string|int|bool $default
     * @return string|int|bool
     */
    public function getAttribute(string $key, string|int|bool $default): string|int|bool
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Push custom attributes to the request,
     * uses for custom request to server.
     *
     * @param array<string, string|int|bool> $pushAttributes Push an attributes as array
     * @return self
     */
    public function with(array $pushAttributes): self
    {
        $this->attributes = array_merge($this->attributes, $pushAttributes);

        return $this;
    }

    /**
     * Get all request as array.
     *
     * @return array<string, mixed> All request
     * @throws Exception
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
     * Get all request and wrap it.
     *
     * @return array<int, array<string, mixed>> Insert all request array in single array
     * @throws Exception
     */
    public function wrap(): array
    {
        return [$this->all()];
    }

    /**
     * Determinate request is ajax.
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return $this->getHeaders('X-Requested-With') == 'XMLHttpRequest';
    }

    /**
     * Determinate request is json request.
     */
    public function isJson(): bool
    {
        /** @var string $contentType */
        $contentType = $this->getHeaders('content-type') ?? '';

        return Str::contains($contentType, '/json') || Str::contains($contentType, '+json');
    }

    /**
     * @return Collection<string, string>
     * @throws Exception
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
     * Get Authorization header.
     *
     * @returm ?string
     */
    public function getAuthorization(): ?string
    {
        return $this->getHeaders('Authorization');
    }

    /**
     * Get Bearer token from Authorization header.
     *
     * @return ?string
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
     * Compine all request input.
     *
     * @template TGetDefault
     * @param TGetDefault $default
     * @return Collection<string, string>|string|TGetDefault
     * @throws Exception
     */
    public function input(?string $key = null, $default = null): Collection|string
    {
        $input = $this->source()->add($this->query->all());
        if (null === $key) {
            return $input;
        }

        return $input->get($key, $default);
    }

    /**
     * Get input resource base on method type.
     *
     * @return Collection<string, string>
     * @throws Exception
     */
    private function source(): Collection
    {
        if ($this->isJson()) {
            return $this->json();
        }

        return in_array($this->method, ['GET', 'HEAD']) ? $this->query : $this->post;
    }

    /**
     * Determine if the given offset exists.
     *
     * @param string $offset
     * @throws Exception
     */
    public function offsetExists($offset): bool
    {
        return $this->source()->has($offset);
    }

    /**
     * Get the value at the given offset.
     *
     * @param string $offset
     * @return string|null
     * @throws Exception
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): ?string
    {
        return $this->__get($offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param string $offset
     * @param string $value
     * @return void
     * @throws Exception
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->source()->set($offset, $value);
    }

    /**
     * Remove the value at the given offset.
     *
     * @param string $offset
     * @return void
     * @throws Exception
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->source()->remove($offset);
    }

    /**
     * Get an input element from the request.
     *
     * @param string $key
     * @return string|null
     * @throws Exception
     */
    public function __get(string $key): ?string
    {
        return $this->all()[$key] ?? null;
    }

    /**
     * Iterator.
     *
     * @return Traversable
     * @throws Exception
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->source()->all());
    }
}
