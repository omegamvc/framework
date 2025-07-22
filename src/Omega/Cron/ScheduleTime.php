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

use Closure;
use Throwable;

use function call_user_func;
use function date;
use function microtime;
use function range;
use function round;

/**
 * Represents a scheduled task that can be executed based on defined time rules.
 *
 * This class encapsulates a callback function to be executed at a specified time,
 * along with its parameters and optional configuration such as retries, naming,
 * logging, and skip conditions. It is used internally by the Schedule manager
 * to handle the logic of task execution.
 *
 * @category  Omega
 * @package   Cron
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class ScheduleTime
{
    /**
     * @var Closure Closure to be executed when the task is due.
     */
    private Closure $callBack;

    /**
     * @var array Parameters to be passed to the callback when executed.
     */
    private array $params;

    /**
     * @var int Reference timestamp used to determine execution time.
     */
    private int $time;

    /**
     * @var string Name assigned to the event (default: "anonymously").
     */
    private string $eventName = 'anonymously';

    /**
     * @var array<int, array<string, int|string>|int> Parsed execution time rules (cron-like).
     */
    private array $timeExec;

    /**
     * @var string A human-readable name for the defined time configuration.
     */
    private string $timeName = '';

    /**
     * @var bool Indicates if the task is anonymous (no name or time label).
     */
    private bool $anonymously = false;

    /**
     * @var bool Set to true if the task execution fails.
     */
    private bool $isFail = false;

    /**
     * @var int Maximum number of retry attempts in case of failure.
     */
    private int $retryAttempts = 0;

    /**
     * @var bool Indicates whether a retry should occur based on conditions.
     */
    private bool $retryCondition = false;

    /**
     * @var bool If true, skips execution based on certain conditions.
     */
    private bool $skip = false;

    /**
     * @var InterpolateInterface|null Optional logger for interpolated messages.
     */
    private ?InterpolateInterface $logger = null;

    /**
     * Constructor.
     *
     * Initializes a new scheduled task with its callback, parameters, and execution timestamp.
     *
     * @param Closure $callBack  The function to be executed.
     * @param array   $params    The parameters to pass to the callback.
     * @param int     $timestamp The reference time for scheduling.
     */
    public function __construct(Closure $callBack, array $params, int $timestamp)
    {
        $this->callBack  = $callBack;
        $this->params    = $params;
        $this->time      = $timestamp;
        $this->timeExec = [
            [
                'D' => date('D', $this->time),
                'd' => date('d', $this->time),
                'h' => date('H', $this->time),
                'm' => date('i', $this->time),
            ],
        ];
    }

    /**
     * Sets the name of the scheduled event.
     *
     * @param string $val The name of the event.
     * @return $this
     */
    public function setEventName(string $val): self
    {
        $this->eventName = $val;

        return $this;
    }

    /**
     * Marks the task as anonymous, hiding it from logs.
     *
     * @param bool $runAsAnonymously Whether to run as anonymously.
     * @return $this
     */
    public function anonymously(bool $runAsAnonymously = true): self
    {
        $this->anonymously = $runAsAnonymously;

        return $this;
    }

    /**
     * Checks if the task is marked as anonymous.
     *
     * @return bool True if anonymous, false otherwise.
     */
    public function isAnonymously(): bool
    {
        return $this->anonymously;
    }

    /**
     * Gets the name of the scheduled event.
     *
     * @return string The event name.
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * Gets the human-readable name of the scheduled time configuration.
     *
     * @return string The time name.
     */
    public function getTimeName(): string
    {
        return $this->timeName;
    }

    /**
     * Retrieves the parsed cron-style time configuration.
     *
     * @return array<int, array<string, int|string>|int> The parsed time rules.
     */
    public function getTimeExec(): array
    {
        return $this->timeExec;
    }

    /**
     * Checks if the last execution resulted in a failure.
     *
     * @return bool True if execution failed, false otherwise.
     */
    public function isFail(): bool
    {
        return $this->isFail;
    }

    /**
     * Gets the number of retry attempts left.
     *
     * @return int Remaining retry attempts.
     */
    public function retryAttempts(): int
    {
        return $this->retryAttempts;
    }

    /**
     * Sets the maximum number of retry attempts.
     *
     * @param int $attempt Number of allowed retries.
     * @return $this
     */
    public function retry(int $attempt): self
    {
        $this->retryAttempts = $attempt;

        return $this;
    }

    /**
     * Sets the condition that determines whether to retry.
     *
     * @param bool $condition True if retry is allowed.
     * @return $this
     */
    public function retryIf(bool $condition): self
    {
        $this->retryCondition = $condition;

        return $this;
    }

    /**
     * Checks whether the task should be retried.
     *
     * @return bool True if retry condition is met, false otherwise.
     */
    public function isRetry(): bool
    {
        return $this->retryCondition;
    }

    /**
     * Marks the task to be skipped under certain conditions.
     *
     * @param bool|Closure(): bool $skipWhen Boolean or closure that returns true if the task should be skipped.
     * @return $this
     */
    public function skip(bool|Closure $skipWhen): self
    {
        if ($skipWhen instanceof Closure) {
            $skipWhen = $skipWhen();
        }

        $this->skip = $skipWhen;

        return $this;
    }

    /**
     * Executes the scheduled task if due and not skipped.
     *
     * Measures execution time, handles exceptions, and optionally logs the execution result.
     *
     * @return void
     */
    public function exec(): void
    {
        if ($this->isDue() && false === $this->skip) {
            // stopwatch
            $watchStart = microtime(true);

            try {
                $outPut              = call_user_func($this->callBack, $this->params) ?? [];
                $this->retryAttempts = 0;
                $this->isFail        = false;
            } catch (Throwable $th) {
                $this->retryAttempts--;
                $this->isFail = true;
                $outPut       = [
                    'error' => $th->getMessage(),
                ];
            }

            // stopwatch
            $watchEnd = round(microtime(true) - $watchStart, 3) * 1000;

            // send command log
            if (!$this->anonymously) {
                $this->logger?->interpolate(
                    $this->eventName,
                    [
                        'execute_time'  => $watchEnd,
                        'cron_time'     => $this->time,
                        'event_name'    => $this->eventName,
                        'attempts'      => $this->retryAttempts,
                        'error_message' => $outPut,
                    ]
                );
            }
        }
    }

    /**
     * Interpolates a message with context values.
     *
     * @param string               $message The message to be logged
     * @param array<string, mixed> $context The context array to replace placeholders
     * @return void
     */
    protected function interpolate(string $message, array $context): void
    {
        // do stuff
    }

    /**
     * Sets the logger instance to be used for interpolation.
     *
     * @param InterpolateInterface $logger The logger instance
     * @return void
     */
    public function setLogger(InterpolateInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Determines whether the current scheduled event is due.
     *
     * @return bool True if the event is due, false otherwise
     */
    public function isDue(): bool
    {
        $events = $this->timeExec;

        $dayLetter  = date('D', $this->time);
        $day        = date('d', $this->time);
        $hour       = date('H', $this->time);
        $minute     = date('i', $this->time);

        foreach ($events as $event) {
            $eventDayLetter = $event['D'] ?? $dayLetter; // default day letter every event

            if (
                $eventDayLetter == $dayLetter
                && $event['d'] == $day
                && $event['h'] == $hour
                && $event['m'] == $minute
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets the execution time to the exact time the object was instantiated.
     *
     * @return $this
     */
    public function justInTime(): self
    {
        $this->timeName  = __FUNCTION__;
        $this->timeExec = [
            [
                'D' => date('D', $this->time),
                'd' => date('d', $this->time),
                'h' => date('H', $this->time),
                'm' => date('i', $this->time),
            ],
        ];

        return $this;
    }

    /**
     * Schedules the event to run every 10 minutes within the same hour and day.
     *
     * @return $this
     */
    public function everyTenMinute(): self
    {
        $this->timeName = __FUNCTION__;
        $minute          = [];
        foreach (range(0, 59) as $time) {
            if ($time % 10 == 0) {
                $minute[] = [
                    'd' => date('d', $this->time),
                    'h' => date('H', $this->time),
                    'm' => $time,
                ];
            }
        }

        $this->timeExec = $minute;

        return $this;
    }

    /**
     * Schedules the event to run every 30 minutes within the same hour and day.
     *
     * @return $this
     */
    public function everyThirtyMinutes(): self
    {
        $this->timeName  = __FUNCTION__;
        $this->timeExec = [
            [
                'd' => date('d', $this->time),
                'h' => date('H', $this->time),
                'm' => 0,
            ],
            [
                'd' => date('d', $this->time),
                'h' => date('H', $this->time),
                'm' => 30,
            ],
        ];

        return $this;
    }

    /**
     * Schedules the event to run every two hours within the same day.
     *
     * @return $this
     */
    public function everyTwoHour(): self
    {
        $this->timeName = __FUNCTION__;

        $thisDay = date('d', $this->time);
        $hourly  = []; // from 00.00 to 23.00 (12 time)
        foreach (range(0, 23) as $time) {
            if ($time % 2 == 0) {
                $hourly[] = [
                    'd' => $thisDay,
                    'h' => $time,
                    'm' => 0,
                ];
            }
        }

        $this->timeExec = $hourly;

        return $this;
    }

    /**
     * Schedules the event to run every twelve hours (midnight and noon) on the same day.
     *
     * @return $this
     */
    public function everyTwelveHour(): self
    {
        $this->timeName  = __FUNCTION__;
        $this->timeExec = [
            [
                'd' => date('d', $this->time),
                'h' => 0,
                'm' => 0,
            ],
            [
                'd' => date('d', $this->time),
                'h' => 12,
                'm' => 0,
            ],
        ];

        return $this;
    }

    /**
     * Schedules the event to run every hour on the same day.
     *
     * @return $this
     */
    public function hourly(): self
    {
        $this->timeName = __FUNCTION__;
        $hourly          = []; // from 00.00 to 23.00 (24 time)
        foreach (range(0, 23) as $time) {
            $hourly[] = [
                'd' => date('d', $this->time),
                'h' => $time,
                'm' => 0,
            ];
        }

        $this->timeExec = $hourly;

        return $this;
    }

    /**
     * Schedules the event to run at a specific hour of the day.
     *
     * @param int $hour24 The hour in 24-hour format (0–23)
     * @return $this
     */
    public function hourlyAt(int $hour24): self
    {
        $this->timeName  = __FUNCTION__;
        $this->timeExec = [
            [
                'd' => date('d', $this->time),
                'h' => $hour24,
                'm' => 0,
            ],
        ];

        return $this;
    }


    /**
     * Schedules the event to run once a day at midnight.
     *
     * @return $this
     */
    public function daily(): self
    {
        $this->timeName  = __FUNCTION__;
        $this->timeExec = [
            // from day 1 to 31 (31 time)
            ['d' => (int) date('d'), 'h' => 0, 'm' => 0],
        ];

        return $this;
    }

    /**
     * Schedules the event to run on a specific day of the month at midnight.
     *
     * @param int $day The day of the month (1–31)
     * @return $this
     */
    public function dailyAt(int $day): self
    {
        $this->timeName  = __FUNCTION__;
        $this->timeExec = [
            [
                'd' => $day,
                'h' => 0,
                'm' => 0,
            ],
        ];

        return $this;
    }

    /**
     * Schedules the event to run weekly on Sunday at midnight.
     *
     * @return $this
     */
    public function weekly(): self
    {
        $this->timeName  = __FUNCTION__;
        $this->timeExec = [
            [
                'D' => 'Sun',
                'd' => date('d', $this->time),
                'h' => 0,
                'm' => 0,
            ],
        ];

        return $this;
    }

    /**
     * Schedules the event to run monthly on the first day at midnight.
     *
     * @return $this
     */
    public function monthly(): self
    {
        $this->timeName  = __FUNCTION__;
        $this->timeExec = [
            [
                'd' => 1,
                'h' => 0,
                'm' => 0,
            ],
        ];

        return $this;
    }
}
