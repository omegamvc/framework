<?php

declare(strict_types=1);

namespace Omega\Console\IO;

/**
 */
class BufferedOutputStream implements OutputStreamInterface
{
    private string $buffer = '';

    /**
     * Empties buffer and returns its content.
     */
    public function fetch(): string
    {
        $content      = $this->buffer;
        $this->buffer = '';

        return $content;
    }

    /**
     * Writes the buffer to the stream.
     */
    public function write(string $buffer): void
    {
        $this->buffer .= $buffer;
    }

    /**
     * Checks whether the stream is interactive (connected to a terminal).
     */
    public function isInteractive(): bool
    {
        return false;
    }
}
