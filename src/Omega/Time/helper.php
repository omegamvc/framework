<?php

declare(strict_types=1);

use Omega\Time\Now;

if (!function_exists('now')) {
    /**
     * Get time object class.
     *
     * @param string      $date_format Set current time
     * @param string|null $time_zone   Set timezone
     * @return Now
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    function now(string $date_format = 'now', ?string $time_zone = null): Now
    {
        return new Now($date_format, $time_zone);
    }
}
