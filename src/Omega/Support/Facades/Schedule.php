<?php

/**
 * Part of Omega - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Facades;

use Closure;
use Omega\Cron\InterpolateInterface;
use Omega\Cron\Schedule as CronSchedule;
use Omega\Cron\ScheduleTime;

/**
 * Facade for the application's task scheduling system.
 *
 * This facade provides a static interface to the scheduler service, allowing you to define,
 * manage, and execute time-based tasks using cron-like expressions or manual callbacks.
 *
 * Common use cases include defining background jobs, time-delayed processes, or recurring tasks
 * like log rotation, report generation, etc.
 *
 * Example:
 * ```php
 * Schedule::call(fn () => Log::info('Task executed!'));
 * Schedule::setTime(60); // Set interval to 60 seconds
 * Schedule::execute();   // Run all scheduled tasks
 * ```
 * @category   Omega
 * @package    Support
 * @subpackage Facades
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 *
 * @method static ScheduleTime[] getPools() Get all registered scheduled tasks.
 * @method static ScheduleTime call(Closure $callBack, array $params = []) Schedule a direct callback with optional parameters.
 * @method static void execute() Execute all scheduled tasks that are due to run.
 * @method static void setLogger(InterpolateInterface $logger) Set a logger implementation for the scheduler.
 * @method static void setTime(int $time) Set the interval (in seconds) at which the scheduler should operate.
 * @method static CronSchedule add(CronSchedule $schedule) Add a new cron schedule to the pool.
 * @method static void flush() Clear all scheduled tasks.
 */
class Schedule extends Facade
{
    /**
     * Get the service accessor key for the scheduler service.
     *
     * This key is used internally by the Facade to resolve the scheduler instance
     * from the service container.
     *
     * @return string The scheduler service accessor key.
     */
    protected static function getAccessor(): string
    {
        return 'schedule';
    }
}
