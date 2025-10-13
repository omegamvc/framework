<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use Closure;
use Omega\Cron\InterpolateInterface;
use Omega\Cron\ScheduleTime;

/**
 * @method static ScheduleTime[]        getPools()
 * @method static ScheduleTime          call(Closure $call_back, mixed[] $params = [])
 * @method static void                  execute()
 * @method static void                  setLogger(InterpolateInterface $logger)
 * @method static void                  setTime(int $time)
 * @method static \Omega\Cron\Schedule  add(\Omega\Cron\Schedule $schedule)
 * @method static void                  flush()
 *
 * @see Schedule
 */
final class Schedule extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'schedule';
    }
}
