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

use ArrayObject;
use Exception;
use InvalidArgumentException;

use function json_decode;
use function json_encode;

use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;

/**
 * JsonResponse represents an HTTP response that contains JSON-encoded data.
 *
 * This class automatically handles encoding arrays or objects into JSON strings,
 * sets appropriate headers, and allows customization of encoding options.
 *
 * @category  Omega
 * @package   Http
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class JsonResponse extends Response
{
    /**
     * The raw JSON string to be sent in the response body.
     *
     * @var string
     */
    protected string $data;

    /**
     * Bitmask of JSON encoding options used with json_encode().
     *
     * Default includes escaping of HTML-sensitive characters.
     *
     * @var int
     */
    protected int $encodingOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

    /**
     * Create a new JSON response instance.
     *
     * @param array|null            $data       The data to encode as JSON.
     * @param int                   $statusCode HTTP status code (default: 200).
     * @param array<string, string> $headers    Headers to include with the response.
     * @return void
     * @throws Exception If the data cannot be JSON encoded.
     */
    public function __construct(?array $data = null, int $statusCode = 200, array $headers = [])
    {
        parent::__construct('', $statusCode, $headers);

        $data ??= new ArrayObject();
        $this->setData($data);
    }

    /**
     * Set the JSON encoding options.
     *
     * Uses the same flags as json_encode().
     *
     * @param int $encodingOptions The bitmask of encoding flags.
     * @return $this Fluent interface.
     *
     * @throws Exception If the current data cannot be re-encoded with the new options.
     */
    public function setEncodingOptions(int $encodingOptions): self
    {
        $this->encodingOptions = $encodingOptions;
        $this->setData(json_decode($this->data));

        return $this;
    }

    /**
     * Get the current JSON encoding options.
     *
     * @return int The bitmask of encoding flags.
     */
    public function getEncodingOptions(): int
    {
        return $this->encodingOptions;
    }

    /**
     * Set the response content directly using a raw JSON string.
     *
     * This bypasses JSON encoding and assumes the string is valid JSON.
     *
     * @param string $json The raw JSON string.
     * @return $this Fluent interface.
     */
    public function setJson(string $json): self
    {
        $this->data = $json;
        $this->prepare();

        return $this;
    }

    /**
     * Set the response content using structured data (array, object, etc.).
     *
     * The data will be encoded to JSON using the current encoding options.
     *
     * @param mixed $data The data to encode as JSON.
     * @return $this Fluent interface.
     *
     * @throws InvalidArgumentException If the data cannot be JSON encoded.
     */
    public function setData(mixed $data): self
    {
        if (false === ($json = json_encode($data, $this->encodingOptions))) {
            throw new InvalidArgumentException('Invalid encode data.');
        }
        $this->data = $json;
        $this->prepare();

        return $this;
    }

    /**
     * Decode and retrieve the response data as an array.
     *
     * @return array The decoded data.
     */
    public function getData(): array
    {
        return json_decode($this->data, true);
    }

    /**
     * Prepare the response by setting the correct content type and body.
     *
     * This method is called internally whenever the response content is updated.
     *
     * @return void
     */
    protected function prepare(): void
    {
        $this->setContentType('application/json');
        $this->setContent($this->data);
    }
}
