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

use Omega\Http\Exceptions\StreamedResponseCallableException;

/**
 * Represents an HTTP response that streams its content to the client.
 *
 * Unlike standard responses, a StreamedResponse allows you to execute a
 * callback that outputs content directly to the client, which is useful
 * for large downloads or dynamically generated output.
 *
 * Extends the base Response class.
 *
 * @category  Omega
 * @package   Http
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class StreamedResponse extends Response
{
    /** @var (callable(): void)|null A callback function that generates and outputs the stream content. */
    private $callableStream;

    /** @var bool Indicates whether the stream has already been sent to the client. */
    private bool $isStream;

    /**
     * Initializes a new streamed response with an optional streaming callback.
     *
     * @param (callable(): void)|null $callableStream The callback that outputs the response body.
     * @param int                     $responseCode    HTTP status code (default is 200 OK).
     * @param array<string, string>   $headers         Headers to send with the response.
     * @return void
     */
    public function __construct(
        $callableStream,
        int $responseCode = Response::HTTP_OK,
        array $headers = [],
    ) {
        $this->setStream($callableStream);
        $this->setResponseCode($responseCode);
        $this->headers   = new HeaderCollection($headers);
        $this->isStream = false;
    }

    /**
     * Sets the callback that will be executed to stream the response content.
     *
     * @param (callable(): void)|null $callableStream The streaming callback.
     * @return self
     */
    public function setStream(?callable $callableStream): self
    {
        $this->callableStream = $callableStream;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws StreamedResponseCallableException If no stream callback is provided.
     */
    protected function sendContent(): void
    {
        if ($this->isStream) {
            return;
        }

        $this->isStream = true;

        if (null === $this->callableStream) {
            throw new StreamedResponseCallableException();
        }

        ($this->callableStream)();
    }
}
