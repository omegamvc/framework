<?php

/** @noinspection PhpUnused */

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

/**
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
 */
class Now
{
    use DateTimeFormatTrait;

    /** @var int|false */
    private int|false $timestamp;

    private DateTime $date;

    /** @var int */
    private int $year;

    /** @var int */
    private int $month;

    /** @var int */
    private int $day;

    /** @var int */
    private int $hour;

    /** @var int */
    private int $minute;

    /** @var int */
    private int $second;

    /** @var string */
    private string $monthName;

    /** @var string */
    private string $dayName;

    /** @var string */
    private string $shortDay;

    /** @var string */
    private string $timeZone;

    private int $age;

    /**
     * Constructor.
     *
     * @param string      $dateFormat
     * @param string|null $timeZone
     * @return void
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
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
     * To string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return implode('T', [
            $this->date->format('Y-m-d'),
            $this->date->format('H:i:s'),
        ]);
    }

    /**
     * Get private property.
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new PropertyNotExistException($name);
    }

    /**
     * Set property by pase the `refresh` logic.
     *
     * @param string $name
     * @param mixed  $value
     * @return void
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
     * Refresh property with current time.
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
     * Current.
     *
     * @param string $format
     * @param int    $timestamp
     * @return string
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
     * Get formated date time.
     *
     * @param string $format
     * @return string
     */
    public function format(string $format): string
    {
        return $this->date->format($format);
    }

    /**
     * Set year time.
     *
     * @param int $year
     * @return self
     */
    public function year(int $year): self
    {
        $this->date
        ->setDate(
            $year,
            $this->month,
            $this->day
        )
        ->setTime(
            $this->hour,
            $this->minute,
            $this->second
        );
        $this->refresh();

        return $this;
    }

    /**
     * set month time.
     *
     * @param int $month
     * @return self
     */
    public function month(int $month): self
    {
        $this->date
        ->setDate(
            $this->year,
            $month,
            $this->day
        )
        ->setTime(
            $this->hour,
            $this->minute,
            $this->second
        );
        $this->refresh();

        return $this;
    }

    /**
     * Set day time.
     *
     * @param int $day
     * @return self
     */
    public function day(int $day): self
    {
        $this->date
        ->setDate(
            $this->year,
            $this->month,
            $day
        )
        ->setTime(
            $this->hour,
            $this->minute,
            $this->second
        );
        $this->refresh();

        return $this;
    }

    /**
     * Set hour time.
     *
     * @param int $hour
     * @return self
     */
    public function hour(int $hour): self
    {
        $this->date
        ->setDate(
            $this->year,
            $this->month,
            $this->day
        )
        ->setTime(
            $hour,
            $this->minute,
            $this->second
        );
        $this->refresh();

        return $this;
    }

    /**
     * Set minute time.
     *
     * @param int $minute
     * @return self
     */
    public function minute(int $minute): self
    {
        $this->date
        ->setDate(
            $this->year,
            $this->month,
            $this->day
        )
        ->setTime(
            $this->hour,
            $minute,
            $this->second
        );
        $this->refresh();

        return $this;
    }

    /**
     * Set second time.
     *
     * @param int $second
     * @return self
     */
    public function second(int $second): self
    {
        $this->date
        ->setDate(
            $this->year,
            $this->month,
            $this->day
        )
        ->setTime(
            $this->hour,
            $this->minute,
            $second
        );
        $this->refresh();

        return $this;
    }

    /**
     * Is Jan.
     *
     * @return bool
     */
    public function isJan(): bool
    {
        return $this->date->format('M') === 'Jan';
    }

    /**
     * Is Feb.
     *
     * @return bool
     */
    public function isFeb(): bool
    {
        return $this->date->format('M') === 'Feb';
    }

    /**
     * Is Mar.
     *
     * @return bool
     */
    public function isMar(): bool
    {
        return $this->date->format('M') === 'Mar';
    }

    /**
     * Is Apr.
     *
     * @return bool
     */
    public function isApr(): bool
    {
        return $this->date->format('M') === 'Apr';
    }

    /**
     * Is May.
     *
     * @return bool
     */
    public function isMay(): bool
    {
        return $this->date->format('M') === 'May';
    }

    /**
     * Is Jun.
     *
     * @return bool
     */
    public function isJun(): bool
    {
        return $this->date->format('M') === 'Jun';
    }

    /**
     * Is Jul.
     *
     * @return bool
     */
    public function isJul(): bool
    {
        return $this->date->format('M') === 'Jul';
    }

    /**
     * Is Aug.
     *
     * @return bool
     */
    public function isAug(): bool
    {
        return $this->date->format('M') === 'Aug';
    }

    /**
     * Is Sep.
     *
     * @return bool
     */
    public function isSep(): bool
    {
        return $this->date->format('M') === 'Sep';
    }

    /**
     * Is Oct.
     *
     * @return bool
     */
    public function isOct(): bool
    {
        return $this->date->format('M') === 'Oct';
    }

    /**
     * Is Nov.
     *
     * @return bool
     */
    public function isNov(): bool
    {
        return $this->date->format('M') === 'Nov';
    }

    /**
     * Is Dec.
     *
     * @return bool
     */
    public function isDec(): bool
    {
        return $this->date->format('M') === 'Dec';
    }

    /**
     * Is Monday.
     *
     * @return bool
     */
    public function isMonday(): bool
    {
        return $this->date->format('D') === 'Mon';
    }

    /**
     * Is Tuesday.
     *
     * @return bool
     */
    public function isTuesday(): bool
    {
        return $this->date->format('D') === 'Tue';
    }

    /**
     * Is Wednesday.
     *
     * @return bool
     */
    public function isWednesday(): bool
    {
        return $this->date->format('D') === 'Wed';
    }

    public function isThursday(): bool
    {
        return $this->date->format('D') == 'Thu';
    }

    /**
     * Is Friday.
     *
     * @return bool
     */
    public function isFriday(): bool
    {
        return $this->date->format('D') == 'Fri';
    }

    /**
     * Is Saturday.
     *
     * @return bool
     */
    public function isSaturday(): bool
    {
        return $this->date->format('D') == 'Sat';
    }

    /**
     * Is Sunday.
     *
     * @return bool
     */
    public function isSunday(): bool
    {
        return $this->date->format('D') == 'Sun';
    }

    /**
     * Is Next Year.
     *
     * @return bool
     */
    public function isNextYear(): bool
    {
        $time = strtotime('next year');

        return $this->current('Y', $time) == $this->year;
    }

    /**
     * Is Next Month.
     *
     * @return bool
     */
    public function isNextMonth(): bool
    {
        $time = strtotime('next month');

        return $this->current('n', $time) == $this->month;
    }

    /**
     * Is Next Day.
     *
     * @return bool
     */
    public function isNextDay(): bool
    {
        $time = strtotime('next day');

        return $this->current('d', $time) == $this->day;
    }

    /**
     * Is Next Hour.
     *
     * @return bool
     */
    public function isNextHour(): bool
    {
        $time = strtotime('next hour');

        return $this->current('H', $time) == $this->hour;
    }

    /**
     * Is Next Minute.
     *
     * @return bool
     */
    public function isNextMinute(): bool
    {
        $time = strtotime('next minute');

        return $this->current('i', $time) == $this->minute;
    }

    /**
     * Is Last Year.
     *
     * @return bool
     */
    public function isLastYear(): bool
    {
        $time = strtotime('last year');

        return $this->current('Y', $time) == $this->year;
    }

    /**
     * Is Last Month.
     *
     * @return bool
     */
    public function isLastMonth(): bool
    {
        $time = strtotime('last month');

        return $this->current('m', $time) == $this->month;
    }

    /**
     * Is Last Day.
     *
     * @return bool
     */
    public function isLastDay(): bool
    {
        $time = strtotime('last day');

        return $this->current('d', $time) == $this->day;
    }

    /**
     * Is Last Hour.
     *
     * @return bool
     */
    public function isLastHour(): bool
    {
        $time = strtotime('last hour');

        return $this->current('H', $time) == $this->hour;
    }

    /**
     * Is Last Minute.
     *
     * @return bool
     */
    public function isLastMinute(): bool
    {
        $time = strtotime('last minute');

        return $this->current('i', $time) == $this->minute;
    }
}
