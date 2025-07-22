<?php

/**
 * Part of Omega - Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Console\Output;

use InvalidArgumentException;
use Omega\Console\Exceptions\InvalidOutputStreamException;
use Omega\Console\Exceptions\OutputWriteException;
use TypeError;

use function fwrite;
use function get_resource_type;
use function is_resource;
use function str_contains;
use function stream_get_meta_data;
use function stream_isatty;

use const STDOUT;

/**
 * Implementation of the OutputStreamInterface that writes to a PHP stream resource.
 *
 * This class is responsible for sending buffered output to a writable stream such as STDOUT.
 * It includes basic validation to ensure the provided stream is suitable for writing,
 * and exposes a method to check whether the stream is interactive (i.e., connected to a terminal).
 *
 * Typical use cases include CLI output formatting and stream abstraction for testing.
 *
 * @category   Omega
 * @package    Console
 * @subpackage Output
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class OutputStream implements OutputStreamInterface
{
    /**
     * @var mixed A writable stream resource or any invalid value used for validation (e.g., string, bool, int).
     */
    private mixed $stream;

    /**
     * OutputStream constructor.
     *
     * Initializes the stream used for writing output. By default, it writes to STDOUT.
     *
     * @param mixed $stream A writable stream resource. If omitted, STDOUT is used.
     * @return void
     * @throws InvalidArgumentException if the provided stream is not a valid writable resource.
     */
    public function __construct(mixed $stream = STDOUT)
    {
        if (!is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new InvalidOutputStreamException();
        }

        $meta = stream_get_meta_data($stream);
        if (str_contains($meta['mode'], 'r') && !str_contains($meta['mode'], '+')) {
            throw new InvalidOutputStreamException('Expected a writable stream');
        }

        $this->stream = $stream;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException if writing to the stream fails
     */
    public function write(string $buffer): void
    {
        try {
            $written = @fwrite($this->stream, $buffer);

            if ($written === false) {
                throw new OutputWriteException('Failed to write to stream');
            }
        } catch (TypeError $e) {
            throw new OutputWriteException('Failed to write to stream', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isInteractive(): bool
    {
        return stream_isatty($this->stream);
    }
}
