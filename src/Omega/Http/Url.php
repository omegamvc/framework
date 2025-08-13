<?php

declare(strict_types=1);

namespace Omega\Http;

use function array_key_exists;
use function parse_str;
use function parse_url;

class Url
{
    private ?string $schema;

    private ?string $host;

    private ?int $port;

    private ?string $user;

    private ?string $password;

    private ?string $path;

    /**
     * @var array<int|string, string>|null
     */
    private ?array $query = null;

    private ?string $fragment;

    /**
     * @param array<string, string|int|array<int|string, string>|null> $parseUrl
     */
    public function __construct(array $parseUrl)
    {
        $this->schema    = $parseUrl['scheme'] ?? null;
        $this->host      = $parseUrl['host'] ?? null;
        $this->port      = $parseUrl['port'] ?? null;
        $this->user      = $parseUrl['user'] ?? null;
        $this->password  = $parseUrl['pass'] ?? null;
        $this->path      = $parseUrl['path'] ?? null;
        $this->fragment  = $parseUrl['fragment'] ?? null;

        if (array_key_exists('query', $parseUrl)) {
            $this->query = $this->parseQuery($parseUrl['query']);
        }
    }

    /**
     * @param string $query
     * @return array<int|string, string>
     */
    private function parseQuery(string $query): array
    {
        $result = [];
        parse_str($query, $result);

        return $result;
    }

    /**
     * @param string $url
     * @return self
     */
    public static function parse(string $url): self
    {
        return new self(parse_url($url));
    }

    public static function fromRequest(Request $from): self
    {
        return new self(parse_url($from->getUrl()));
    }

    /**
     * @return array|int|string|null
     */
    public function schema(): array|int|string|null
    {
        return $this->schema;
    }

    /**
     * @return array|int|string|null
     */
    public function host(): array|int|string|null
    {
        return $this->host;
    }

    /**
     * @return array|int|string|null
     */
    public function port(): array|int|string|null
    {
        return $this->port;
    }

    /**
     * @return array|int|string|null
     */
    public function user(): array|int|string|null
    {
        return $this->user;
    }

    /**
     * @return array|int|string|null
     */
    public function password(): array|int|string|null
    {
        return $this->password;
    }

    /**
     * @return array|int|string|null
     */
    public function path(): array|int|string|null
    {
        return $this->path;
    }

    /**
     * @return array<int|string, string>|null
     */
    public function query(): ?array
    {
        return $this->query;
    }

    /**
     * @return array|int|string|null
     */
    public function fragment(): array|int|string|null
    {
        return $this->fragment;
    }

    public function hasSchema(): bool
    {
        return null !== $this->schema;
    }

    public function hasHost(): bool
    {
        return null !== $this->host;
    }

    public function hasPort(): bool
    {
        return null !== $this->port;
    }

    public function hasUser(): bool
    {
        return null !== $this->user;
    }

    public function hasPassword(): bool
    {
        return null !== $this->password;
    }

    public function hasPath(): bool
    {
        return null !== $this->path;
    }

    public function hasQuery(): bool
    {
        return null !== $this->query;
    }

    public function hasFragment(): bool
    {
        return null !== $this->fragment;
    }
}
