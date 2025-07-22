<?php

/**
 * Part of Omega - Cron Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Cron;

/**
 * Interface for interpolating messages with contextual data.
 *
 * Classes implementing this interface are expected to provide a way
 * to handle and format messages by replacing placeholders in a message
 * string with values from a context array.
 *
 * @category  Omega
 * @package   Cron
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
interface InterpolateInterface
{
    /**
     * Interpolates the given message using the provided context.
     *
     * Placeholders in the message (e.g. {key}) will be replaced by matching values
     * from the context array. This is commonly used for logging and formatted output.
     *
     * @param string $message The message containing placeholders.
     * @param array<string, mixed> $context Key-value pairs to replace in the message.
     * @return void
     */
    public function interpolate(string $message, array $context = []): void;
}
