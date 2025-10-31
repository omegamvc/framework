<?php

/**
 * Part of Omega - Cron Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Cron;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use Omega\Support\Facades\DB;

use function Omega\Time\now;
use function json_encode;

/**
 * Class Log
 *
 * Implements the `InterpolateInterface` to handle logging for scheduled cron tasks.
 * This implementation persists log messages and their contextual data into a database table (`cron`).
 *
 * Each call to `interpolate()` inserts a new record containing:
 * - The message string
 * - The serialized context array (as JSON)
 * - The timestamp of when the log entry was created
 *
 * This class may throw:
 * - `DateMalformedStringException` if the timestamp generation fails
 * - `DateInvalidTimeZoneException` if the system cannot determine the correct timezone
 *
 * Usage example:
 * ```php
 * $logger = new Log();
 * $logger->interpolate('Task executed', ['task_id' => 123]);
 * ```
 *
 * @category  Omega
 * @package   Cron
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class Log implements InterpolateInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     */
    public function interpolate(string $message, array $context = []): void
    {
        DB::table('cron')
            ->insert()
            ->values([
                'message'     => $message,
                'context'     => json_encode($context),
                'date_create' => now()->timestamp,
            ])
            ->execute();
    }
}
