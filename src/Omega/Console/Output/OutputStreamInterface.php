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

/**
 * Interface for writing output to a given stream.
 *
 * This abstraction allows sending output (e.g., CLI messages)
 * to different destinations such as STDOUT, STDERR, or a buffered stream
 * for testing and formatting purposes.
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
interface OutputStreamInterface
{
    /**
     * Write a string buffer to the output stream.
     *
     * @param string $buffer The content to be written.
     * @return void
     */
    public function write(string $buffer): void;

    /**
     * Determine whether the output stream is interactive.
     *
     * This typically means the stream is attached to a TTY device.
     *
     * @return bool True if the stream is interactive, false otherwise.
     */
    public function isInteractive(): bool;
}
