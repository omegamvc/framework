<?php

/**
 * Part of Omega - Time Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Time;

use DateInvalidTimeZoneException;
use DateMalformedStringException;

/**
 * Time helper functions.
 *
 * This file provides convenient shortcuts for working with the current date and time
 * by instantiating the `Now` class, which wraps PHP's DateTime and adds utility methods.
 *
 * @category  Omega
 * @package   Time
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

if (!function_exists('now')) {
    /**
     * Create a new instance of the Now class.
     *
     * Returns an object representing the current date and time, or a custom datetime,
     * optionally using a specific timezone. This helper wraps PHP's DateTime with
     * additional utility methods via the Now class.
     *
     * @param string      $date_format Optional. A valid datetime string (default: 'now').
     * @param string|null $time_zone   Optional. A valid timezone identifier.
     * @throws DateInvalidTimeZoneException If the timezone string is invalid.
     * @throws DateMalformedStringException If the date format is not parseable.
     */
    function now(string $date_format = 'now', ?string $time_zone = null): Now
    {
        return new Now($date_format, $time_zone);
    }
}
