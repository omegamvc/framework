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
use DateTime;
use DateTimeZone;
use Omega\Time\Exceptions\PropertyNotExistException;
use Omega\Time\Exceptions\PropertyNotSettableException;
use Omega\Time\Traits\DateTimeFormatTrait;

use function floor;
use function implode;
use function max;
use function method_exists;
use function property_exists;
use function strtotime;
use function time;

/**
 * Class Now
 *
 * Represents an enhanced and mutable date-time object.
 *
 * The `Now` class wraps a native {@see DateTime} instance, enriching it with:
 * - Immutable snapshot of time with formatted properties.
 * - Custom getter/setter access via magic methods.
 * - Convenient checks for time comparison (e.g. `isNextMonth()`, `isSunday()`).
 * - Quick access to commonly used temporal components (year, month, day, etc.).
 * - Various standard string formatters via {@see DateTimeFormatTrait}.
 *
 * It is useful for simplifying time handling in business logic, human-friendly formatting,
 * and performing calendar-based checks without manually parsing date values.
 *
 * @category  Omega
 * @package   Time
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @property int    $timestamp
 * @property int    $year
 * @property int    $month
 * @property int    $day
 * @property int    $hour
 * @property int    $minute
 * @property int    $second
 * @property string $monthName
 * @property string $dayName
 * @property string $shortDay
 * @property string $timeZone
 * @property int    $age
 * @property mixed  $notExistProperty
 */
class Now
{
    use DateTimeFormatTrait;

    /** @var DateTime Internal DateTime instance that holds the core date-time value. */
    private DateTime $date;

    /** @var int|false The Unix timestamp corresponding to the current date. */
    private int|false $timestamp;

    /** @var int The current year (e.g., 2025). */
    private int $year;

    /** @var int The current month number (1–12). */
    private int $month;

    /** @var int The current day of the month (1–31). */
    private int $day;

    /** @var int The current hour in 24-hour format (0–23). */
    private int $hour;

    /** @var int The current minute (0–59). */
    private int $minute;

    /** @var int The current second (0–59). */
    private int $second;

    /** @var string Full textual representation of the month (e.g., "January"). */
    private string $monthName;

    /** @var string Full textual representation of the day of the week (e.g., "Tuesday"). */
    private string $dayName;

    /** @var string Short (three-letter) day name (e.g., "Tue"). */
    private string $shortDay;

    /** @var string Name of the current time zone (e.g., "UTC", "Europe/Rome"). */
    private string $timeZone;

    /**
     * Calculated age based on the given date (used when a birthdate-like value is passed).
     * Computed as full years between `$date` and now.
     *
     * @var int
     */
    private int $age;

    /**
     * Creates a new Now instance representing the given date and time.
     *
     * @param string      $dateFormat A valid date string (e.g., 'now', '2023-01-01', 'tomorrow').
     * @param string|null $timeZone   Optional timezone identifier (e.g., 'UTC', 'Europe/Rome').
     * @return void
     * @throws DateInvalidTimeZoneException If the timezone string is invalid.
     * @throws DateMalformedStringException If the date format is not parseable.
     */
    public function __construct(string $dateFormat = 'now', ?string $timeZone = null)
    {
        if (null !== $timeZone) {
            $timeZone = new DateTimeZone($timeZone);
        }

        $this->date = new DateTime($dateFormat, $timeZone);

        $this->refresh();
    }

    /**
     * Returns the datetime in ISO 8601 format (e.g., "2025-06-25T14:30:00").
     *
     * @return string The string representation of the date and time.
     */
    public function __toString(): string
    {
        return implode('T', [
            $this->date->format('Y-m-d'),
            $this->date->format('H:i:s'),
        ]);
    }

    /**
     * Dynamically retrieves a private property value.
     *
     * @param string $name The name of the property.
     * @return mixed The value of the property.
     * @throws PropertyNotExistException If the requested property does not exist.
     */
    public function __get(string $name): mixed
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new PropertyNotExistException($name);
    }

    /**
     * Dynamically sets a private property using its corresponding setter method.
     *
     * @param string $name  The name of the property.
     * @param mixed  $value The value to set.
     * @return void
     * @throws PropertyNotSettableException If the property cannot be set or does not have a setter.
     */
    public function __set(string $name, mixed $value): void
    {
        if (method_exists($this, $name) && property_exists($this, $name)) {
            $this->{$name}($value);

            return;
        }

        throw new PropertyNotSettableException($name);
    }

    /**
     * Refreshes all cached property values (year, month, day, etc.) based on the internal DateTime object.
     *
     * @return void
     */
    private function refresh(): void
    {
        $this->timestamp = $this->date->getTimestamp();
        $this->year      = (int) $this->date->format('Y');
        $this->month     = (int) $this->date->format('n');
        $this->day       = (int) $this->date->format('d');
        $this->hour      = (int) $this->date->format('H');
        $this->minute    = (int) $this->date->format('i');
        $this->second    = (int) $this->date->format('s');

        $this->monthName = $this->date->format('F');
        $this->dayName   = $this->date->format('l');
        $this->timeZone  = $this->date->format('e');
        $this->shortDay  = $this->date->format('D');

        $this->age = max(0, (int) floor((time() - $this->timestamp) / (365.25 * 24 * 60 * 60)));
    }

    /**
     * Returns a formatted date string for a given timestamp.
     *
     * @param string $format    The format string (compatible with DateTime::format).
     * @param int    $timestamp The Unix timestamp to format.
     * @return string The formatted date string.
     */
    private function current(string $format, int $timestamp): string
    {
        $date = $this->date;

        return $date
            ->setTimestamp($timestamp)
            ->format($format)
        ;
    }

    /**
     * Formats the current date and time using a custom format string.
     *
     * @param string $format The format string (e.g., 'Y-m-d H:i:s').
     * @return string The formatted date/time string.
     */
    public function format(string $format): string
    {
        return $this->date->format($format);
    }

    /**
     * Sets the year part of the date.
     *
     * @param int $year The year to set (e.g., 2025).
     * @return self The current instance for method chaining.
     */
    public function year(int $year): self
    {
        $this->date
            ->setDate($year, $this->month, $this->day)
            ->setTime($this->hour, $this->minute, $this->second);

        $this->refresh();

        return $this;
    }

    /**
     * Sets the month part of the date.
     *
     * @param int $month The month to set (1–12).
     * @return self The current instance for method chaining.
     */
    public function month(int $month): self
    {
        $this->date
            ->setDate($this->year, $month, $this->day)
            ->setTime($this->hour, $this->minute, $this->second);

        $this->refresh();

        return $this;
    }

    /**
     * Sets the day part of the date.
     *
     * @param int $day The day to set (1–31, depending on the month/year).
     * @return self The current instance for method chaining.
     */
    public function day(int $day): self
    {
        $this->date
            ->setDate($this->year, $this->month, $day)
            ->setTime($this->hour, $this->minute, $this->second);

        $this->refresh();

        return $this;
    }

    /**
     * Sets the hour component of the date.
     *
     * @param int $hour The hour to set (0–23).
     * @return self The current instance for method chaining.
     */
    public function hour(int $hour): self
    {
        $this->date
            ->setDate($this->year, $this->month, $this->day)
            ->setTime($hour, $this->minute, $this->second);

        $this->refresh();

        return $this;
    }

    /**
     * Sets the minute component of the date.
     *
     * @param int $minute The minute to set (0–59).
     * @return self The current instance for method chaining.
     */
    public function minute(int $minute): self
    {
        $this->date
            ->setDate($this->year, $this->month, $this->day)
            ->setTime($this->hour, $minute, $this->second);

        $this->refresh();

        return $this;
    }

    /**
     * Sets the second component of the date.
     *
     * @param int $second The second to set (0–59).
     * @return self The current instance for method chaining.
     */
    public function second(int $second): self
    {
        $this->date
            ->setDate($this->year, $this->month, $this->day)
            ->setTime($this->hour, $this->minute, $second);

        $this->refresh();

        return $this;
    }

    /**
     * Checks if the current month is January.
     * @return bool True if it's January, false otherwise.
     */
    public function isJan(): bool
    {
        return $this->date->format('M') === 'Jan';
    }

    /**
     * Checks if the current month is February.
     *
     * @return bool True if it's February, false otherwise.
     */
    public function isFeb(): bool
    {
        return $this->date->format('M') === 'Feb';
    }

    /**
     * Checks if the current month is March.
     *
     * @return bool True if it's March, false otherwise.
     */
    public function isMar(): bool
    {
        return $this->date->format('M') === 'Mar';
    }

    /**
     * Checks if the current month is April.
     *
     * @return bool True if it's April, false otherwise.
     */
    public function isApr(): bool
    {
        return $this->date->format('M') === 'Apr';
    }

    /**
     * Checks if the current month is May.
     *
     * @return bool True if it's May, false otherwise.
     */
    public function isMay(): bool
    {
        return $this->date->format('M') === 'May';
    }

    /**
     * Checks if the current month is June.
     *
     * @return bool True if it's June, false otherwise.
     */
    public function isJun(): bool
    {
        return $this->date->format('M') === 'Jun';
    }

    /**
     * Checks if the current month is July.
     *
     * @return bool True if it's July, false otherwise.
     */
    public function isJul(): bool
    {
        return $this->date->format('M') === 'Jul';
    }

    /**
     * Checks if the current month is August.
     *
     * @return bool True if it's August, false otherwise.
     */
    public function isAug(): bool
    {
        return $this->date->format('M') === 'Aug';
    }

    /**
     * Checks if the current month is September.
     *
     * @return bool True if it's September, false otherwise.
     */
    public function isSep(): bool
    {
        return $this->date->format('M') === 'Sep';
    }

    /**
     * Checks if the current month is October.
     *
     * @return bool True if it's October, false otherwise.
     */
    public function isOct(): bool
    {
        return $this->date->format('M') === 'Oct';
    }

    /**
     * Checks if the current month is November.
     *
     * @return bool True if it's November, false otherwise.
     */
    public function isNov(): bool
    {
        return $this->date->format('M') === 'Nov';
    }

    /**
     * Checks if the current month is December.
     *
     * @return bool True if it's December, false otherwise.
     */
    public function isDec(): bool
    {
        return $this->date->format('M') === 'Dec';
    }

    /**
     * Checks if the current day is Monday.
     *
     * @return bool True if it's Monday, false otherwise.
     */
    public function isMonday(): bool
    {
        return $this->date->format('D') === 'Mon';
    }

    /**
     * Checks if the current day is Tuesday.
     *
     * @return bool True if it's Tuesday, false otherwise.
     */
    public function isTuesday(): bool
    {
        return $this->date->format('D') === 'Tue';
    }

    /**
     * Checks if the current day is Wednesday.
     *
     * @return bool True if it's Wednesday, false otherwise.
     */
    public function isWednesday(): bool
    {
        return $this->date->format('D') === 'Wed';
    }

    /**
     * Checks if the current day is Thursday.
     *
     * @return bool True if it's Thursday, false otherwise.
     */
    public function isThursday(): bool
    {
        return $this->date->format('D') == 'Thu';
    }

    /**
     * Checks if the current day is Friday.
     *
     * @return bool True if it's Friday, false otherwise.
     */
    public function isFriday(): bool
    {
        return $this->date->format('D') == 'Fri';
    }

    /**
     * Checks if the current day is Saturday.
     *
     * @return bool True if it's Saturday, false otherwise.
     */
    public function isSaturday(): bool
    {
        return $this->date->format('D') == 'Sat';
    }

    /**
     * Checks if the current day is Sunday.
     *
     * @return bool True if it's Sunday, false otherwise.
     */
    public function isSunday(): bool
    {
        return $this->date->format('D') == 'Sun';
    }

    /**
     * Checks if the current year is the next calendar year.
     *
     * @return bool True if it matches the next year, false otherwise.
     */
    public function isNextYear(): bool
    {
        $time = strtotime('next year');

        return $this->current('Y', $time) == $this->year;
    }

    /**
     * Checks if the current month is the next calendar month.
     *
     * @return bool True if it matches the next month, false otherwise.
     */
    public function isNextMonth(): bool
    {
        $time = strtotime('next month');

        return $this->current('n', $time) == $this->month;
    }

    /**
     * Checks if the current day is the next calendar day.
     *
     * @return bool True if it matches the next day, false otherwise.
     */
    public function isNextDay(): bool
    {
        $time = strtotime('next day');

        return $this->current('d', $time) == $this->day;
    }

    /**
     * Checks if the current hour is the next hour.
     *
     * @return bool True if it matches the next hour, false otherwise.
     */
    public function isNextHour(): bool
    {
        $time = strtotime('next hour');

        return $this->current('H', $time) == $this->hour;
    }

    /**
     * Checks if the current minute is the next minute.
     *
     * @return bool True if it matches the next minute, false otherwise.
     */
    public function isNextMinute(): bool
    {
        $time = strtotime('next minute');

        return $this->current('i', $time) == $this->minute;
    }

    /**
     * Checks if the current year is the previous calendar year.
     *
     * @return bool True if it matches the last year, false otherwise.
     */
    public function isLastYear(): bool
    {
        $time = strtotime('last year');

        return $this->current('Y', $time) == $this->year;
    }

    /**
     * Checks if the current month is the previous calendar month.
     *
     * @return bool True if it matches the last month, false otherwise.
     */
    public function isLastMonth(): bool
    {
        $time = strtotime('last month');

        return $this->current('m', $time) == $this->month;
    }

    /**
     * Checks if the current day is the previous calendar day.
     *
     * @return bool True if it matches the last day, false otherwise.
     */
    public function isLastDay(): bool
    {
        $time = strtotime('last day');

        return $this->current('d', $time) == $this->day;
    }

    /**
     * Checks if the current hour is the previous hour.
     *
     * @return bool True if it matches the last hour, false otherwise.
     */
    public function isLastHour(): bool
    {
        $time = strtotime('last hour');

        return $this->current('H', $time) == $this->hour;
    }

    /**
     * Checks if the current minute is the previous minute.
     *
     * @return bool True if it matches the last minute, false otherwise.
     */
    public function isLastMinute(): bool
    {
        $time = strtotime('last minute');

        return $this->current('i', $time) == $this->minute;
    }
}
