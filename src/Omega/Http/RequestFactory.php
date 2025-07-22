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

use function apache_request_headers;
use function array_change_key_case;
use function file_get_contents;
use function function_exists;
use function preg_match;
use function strncmp;
use function strtr;
use function substr;
use function trim;

/**
 * Factory class responsible for creating the HTTP request object
 * from PHP superglobals at the very beginning of the application lifecycle.
 *
 * This class is used to capture the initial request state and normalize it
 * into a consistent Request object used by the framework.
 *
 * It is typically invoked directly in the front controller (e.g., `public/index.php`)
 * before any middleware or kernel logic is executed.
 *
 * @category  Omega
 * @package   Http
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class RequestFactory
{
    /**
     * Creates and returns a Request object using global PHP variables.
     *
     * This is the main entry point used to capture the current HTTP request.
     *
     * @return Request The fully populated Request object.
     */
    public static function capture(): Request
    {
        return (new self())->getFromGlobal();
    }

    /**
     * Builds a Request instance using the current PHP superglobals.
     *
     * @return Request A new Request instance populated with server data.
     */
    public function getFromGlobal(): Request
    {
        return new Request(
            $_SERVER['REQUEST_URI'] ?? '',
            $_GET,
            $_POST,
            [],
            $_COOKIE,
            $_FILES,
            $this->getHeaders(),
            $this->getMethod(),
            $this->getClient(),
            $this->getRawBody()
        );
    }

    /**
     * Parses and normalizes HTTP headers from server variables.
     *
     * Handles support for both Apache and CGI/FastCGI setups and ensures
     * the `Authorization` header is reconstructed if missing.
     *
     * @return array<string, string> The normalized headers array.
     */
    private function getHeaders(): array
    {
        if (function_exists('apache_request_headers')) {
            return array_change_key_case(
                apache_request_headers()
            );
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strncmp($key, 'HTTP_', 5) === 0) {
                $key = substr($key, 5);
            } elseif (strncmp($key, 'CONTENT_', 8)) {
                continue;
            }
            $headers[strtr($key, '_', '-')] = $value;
        }

        if (!isset($headers['Authorization'])) {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass               = $_SERVER['PHP_AUTH_PW'] ?? '';
                $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

        return array_change_key_case($headers);
    }

    /**
     * Determines the HTTP method from the request.
     *
     * Supports method override via `X-HTTP-Method-Override` header for POST requests.
     *
     * @return string|null The detected HTTP method (e.g., GET, POST, PUT).
     */
    private function getMethod(): ?string
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? null;
        if (
            $method === 'POST'
            && preg_match('#^[A-Z]+$#D', $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '')
        ) {
            $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
        }

        return $method;
    }

    /**
     * Retrieves the client IP address from server variables.
     *
     * @return string|null The IP address of the client, or null if unavailable.
     */
    private function getClient(): ?string
    {
        return !empty($_SERVER['REMOTE_ADDR'])
            ? trim($_SERVER['REMOTE_ADDR'], '[]')
            : null;
    }

    /**
     * Reads the raw body content from the input stream.
     *
     * Used for capturing JSON, XML, or other non-form POST payloads.
     *
     * @return string|null The raw request body, or null if empty.
     */
    private function getRawBody(): ?string
    {
        return file_get_contents('php://input') ?: null;
    }
}
