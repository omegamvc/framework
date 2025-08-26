<?php

declare(strict_types=1);

namespace Omega\Console\IO;

use Omega\Console\Exceptions\InvalidStreamException;

use function fwrite;
use function get_resource_type;
use function is_resource;
use function str_contains;
use function stream_get_meta_data;
use function stream_isatty;

use const STDOUT;

/**
 */
class OutputStream implements OutputStreamInterface
{
    /**
     * @var resource
     */
    private mixed $stream;

    /**
     * ResourceOutputStream constructor.
     *
     * @param false|resource $stream
     * @return void
     * @throws InvalidStreamException if the stream is not a valid or writable resource
     */
    public function __construct(mixed $stream = STDOUT)
    {
        if (!is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new InvalidStreamException('Expected a valid stream');
        }

        $meta = stream_get_meta_data($stream);
        if (str_contains($meta['mode'], 'r') && !str_contains($meta['mode'], '+')) {
            throw new InvalidStreamException('Expected a writable stream');
        }

        $this->stream = $stream;
    }

    /**
     * Writes the buffer to the stream.
     *
     * @param string $buffer
     * @return void
     * @throws InvalidStreamException if writing to the stream fails
     */
    public function write(string $buffer): void
    {
        if (fwrite($this->stream, $buffer) === false) {
            throw new InvalidStreamException('Failed to write to stream');
        }
    }

    /**
     * Checks whether the stream is interactive (connected to a terminal).
     *
     * @return bool
     */
    public function isInteractive(): bool
    {
        return stream_isatty($this->stream);
    }
}
