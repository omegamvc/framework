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
use Omega\Collection\Collection;
use Omega\Text\Str;

use function array_map;
use function explode;
use function implode;
use function is_array;
use function is_int;
use function preg_split;
use function rtrim;
use function sprintf;
use function str_contains;
use function str_ends_with;
use function str_starts_with;
use function substr;
use function trim;

/**
 * Represents a collection of HTTP headers with support for directive parsing and manipulation.
 *
 * Extends the base Collection class to provide helper methods for:
 * - Parsing complex header values (e.g. Cache-Control, Accept)
 * - Adding, removing, and checking individual directive items
 * - Working with raw header strings
 *
 * Useful for manipulating headers in a structured way while preserving formatting standards.
 *
 * @category  Omega
 * @package   Http
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @extends Collection<string, string>
 */
class HeaderCollection extends Collection
{
    /**
     * Constructs a new HeaderCollection instance.
     *
     * @param array<string, string> $headers An associative array of headers
     *                                       (e.g., ['Content-Type' => 'application/json']).
     * @retun void
     */
    public function __construct(array $headers)
    {
        parent::__construct($headers);
    }

    /**
     * Converts the header collection to a string representation.
     *
     * @return string A string of headers formatted as "Key: Value\r\n"
     */
    public function __toString(): string
    {
        $headers = $this->clone()->map(fn (string $value, string $key = ''): string => "$key: $value")->toArray();

        return implode("\r\n", $headers);
    }

    /**
     * Sets a raw header string in the collection (e.g., "X-Custom: Foo").
     *
     * @param string $header The raw header string to set.
     * @return $this
     *
     * @throws Exception If the header does not contain a ":" separator.
     */
    public function setRaw(string $header): self
    {
        if (false === Str::contains($header, ':')) {
            throw new Exception(sprintf('Invalid header structure %s.', $header));
        }

        [$headerName, $headerVal] = explode(':', $header, 2);

        return $this->set(trim($headerName), trim($headerVal));
    }

    /**
     * Retrieves the parsed directive values from a complex header (e.g. Cache-Control).
     *
     * @param string $header The name of the header to parse.
     * @return array<string|int, string|string[]> An associative or indexed array of header items.
     */
    public function getDirective(string $header): array
    {
        return $this->parseDirective($header);
    }

    /**
     * Adds one or more values to an existing header directive.
     *
     * If the header does not exist, it will be created.
     *
     * @param string                             $header The header name (e.g., "Cache-Control").
     * @param array<int|string, string|string[]> $value  The directive values to add.
     * @return $this
     */
    public function addDirective(string $header, array $value): self
    {
        $items = $this->parseDirective($header);
        foreach ($value as $key => $newItem) {
            if (is_int($key)) {
                $items[] = $newItem;
                continue;
            }
            $items[$key] = $newItem;
        }

        return $this->set($header, $this->encodeToString($items));
    }

    /**
     * Removes a specific directive or directive key from a header.
     *
     * @param string $header The header name (e.g., "Cache-Control").
     * @param string $item   The directive key or value to remove.
     * @return $this
     */
    public function removeDirective(string $header, string $item): self
    {
        $items     = $this->parseDirective($header);
        $newItems = [];
        foreach ($items as $key => $value) {
            if ($key === $item) {
                continue;
            }
            if ($value === $item) {
                continue;
            }
            $newItems[$key] = $value;
        }

        return $this->set($header, $this->encodeToString($newItems));
    }

    /**
     * Checks if a directive or key exists in a header.
     *
     * @param string $header The header name.
     * @param string $item   The directive name or value to search for.
     * @return bool True if found; otherwise false.
     */
    public function hasDirective(string $header, string $item): bool
    {
        $items = $this->parseDirective($header);
        foreach ($items as $key => $value) {
            if ($key === $item) {
                return true;
            }
            if ($value === $item) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parses a complex header value into a structured array.
     *
     * Handles comma-separated values and key-value directives (with optional quoted sub-items).
     *
     * @param string $key The name of the header to parse.
     * @return array<string|int, string|string[]> Parsed header content.
     */
    private function parseDirective(string $key): array
    {
        if (false === $this->has($key)) {
            return [];
        }

        $header      = $this->get($key);
        $pattern     = '/,\s*(?=(?:[^\"]*\"[^\"]*\")*[^\"]*$)/';
        $headerItem  = preg_split($pattern, $header);

        $result = [];
        foreach ($headerItem as $item) {
            if (str_contains($item, '=')) {
                $parts = explode('=', $item, 2);
                $key   = trim($parts[0]);
                $value = trim($parts[1]);
                if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                    $value = substr($value, 1, -1);
                    $value = array_map('trim', explode(', ', $value));
                }
                $result[$key] = $value;
                continue;
            }
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Converts an associative or indexed array of directives into a header-compliant string.
     *
     * Values that are arrays are quoted and joined with commas.
     *
     * @param array<string|int, string|string[]> $data The data to encode.
     * @return string The encoded string suitable for a header value.
     */
    private function encodeToString(array $data): string
    {
        $encodedString = '';

        foreach ($data as $key => $value) {
            if (is_int($key)) {
                $encodedString .= $value . ', ';
                continue;
            }

            if (is_array($value)) {
                $value = '"' . implode(', ', $value) . '"';
            }
            $encodedString .= $key . '=' . $value . ', ';
        }

        return rtrim($encodedString, ', ');
    }
}
