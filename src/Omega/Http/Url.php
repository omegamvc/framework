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

use function array_key_exists;
use function parse_str;
use function parse_url;

/**
 * A class representing a parsed URL, providing access to its components
 * such as scheme, host, port, user, password, path, query, and fragment.
 *
 * Instances of this class are typically created using `Url::parse()` or `Url::fromRequest()`.
 *
 * @category  Omega
 * @package   Http
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class Url
{
    /** @var string|null The URL scheme (e.g., "http", "https"). */
    private ?string $schema;

    /** @var string|null The host portion of the URL (e.g., "example.com"). */
    private ?string $host;

    /** @var string|null The port number used in the URL, if specified. */
    private ?int $port;

    /** @var string|null The user component of the URL, if authentication is embedded. */
    private ?string $user;

    /** @var string|null The password component of the URL, if authentication is embedded. */
    private ?string $password;

    /** @var string|null The path portion of the URL (e.g., "/path/to/resource"). */
    private ?string $path;

    /** @var array<int|string, string>|null The parsed query string parameters as a key-value array. */
    private ?array $query = null;

    /** @var string|null The URL fragment (i.e., everything after the `#` symbol). */
    private ?string $fragment;

    /**
     * Initializes a new Url instance from the given array of parsed URL components.
     *
     * @param array<string, string|int|array<int|string, string>|null> $parseUrl The parsed URL components
     *                                                                           from parse_url().
     * @return void
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
     * Parses a query string into an associative array.
     *
     * @param string $query The raw query string (e.g., "foo=bar&baz=qux").
     * @return array<int|string, string> The resulting key-value pairs.
     */
    private function parseQuery(string $query): array
    {
        $result = [];
        parse_str($query, $result);

        return $result;
    }

    /**
     * Creates a new Url instance by parsing a raw URL string.
     *
     * @param string $url The URL to parse.
     * @return self
     */
    public static function parse(string $url): self
    {
        return new self(parse_url($url));
    }

    /**
     * Creates a new Url instance from a Request object.
     *
     * @param Request $from The Request object from which to extract the URL.
     * @return self
     */
    public static function fromRequest(Request $from): self
    {
        return new self(parse_url($from->getUrl()));
    }

    /**
     * Returns the URL scheme (e.g., "http").
     *
     * @return array|int|string|null
     */
    public function schema(): array|int|string|null
    {
        return $this->schema;
    }

    /**
     * Returns the host name (e.g., "example.com").
     *
     * @return array|int|string|null
     */
    public function host(): array|int|string|null
    {
        return $this->host;
    }

    /**
     * Returns the port number if specified.
     *
     * @return array|int|string|null
     */
    public function port(): array|int|string|null
    {
        return $this->port;
    }

    /**
     * Returns the username part of the URL (if any).
     *
     * @return array|int|string|null
     */
    public function user(): array|int|string|null
    {
        return $this->user;
    }

    /**
     * Returns the password part of the URL (if any).
     *
     * @return array|int|string|null
     */
    public function password(): array|int|string|null
    {
        return $this->password;
    }

    /**
     * Returns the path component of the URL.
     *
     * @return array|int|string|null
     */
    public function path(): array|int|string|null
    {
        return $this->path;
    }

    /**
     * Returns the parsed query parameters, or null if none.
     *
     * @return array<int|string, string>|null
     */
    public function query(): ?array
    {
        return $this->query;
    }

    /**
     * Returns the URL fragment, or null if none is present.
     *
     * @return array|int|string|null
     */
    public function fragment(): array|int|string|null
    {
        return $this->fragment;
    }

    /**
     * Checks whether the URL contains a scheme.
     *
     * @return bool
     */
    public function hasSchema(): bool
    {
        return null !== $this->schema;
    }

    /**
     * Checks whether the URL contains a host.
     *
     * @return bool
     */
    public function hasHost(): bool
    {
        return null !== $this->host;
    }

    /**
     * Checks whether the URL contains a port number.
     *
     * @return bool
     */
    public function hasPort(): bool
    {
        return null !== $this->port;
    }

    /**
     * Checks whether the URL contains a user component.
     *
     * @return bool
     */
    public function hasUser(): bool
    {
        return null !== $this->user;
    }

    /**
     * Checks whether the URL contains a password component.
     *
     * @return bool
     */
    public function hasPassword(): bool
    {
        return null !== $this->password;
    }

    /**
     * Checks whether the URL contains a path.
     *
     * @return bool
     */
    public function hasPath(): bool
    {
        return null !== $this->path;
    }

    /**
     * Checks whether the URL contains a query string.
     *
     * @return bool
     */
    public function hasQuery(): bool
    {
        return null !== $this->query;
    }

    /**
     * Checks whether the URL contains a fragment.
     *
     * @return bool
     */
    public function hasFragment(): bool
    {
        return null !== $this->fragment;
    }
}
