<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

/**
 * @method static \Omega\Cron\ScheduleTime[] getPools()
 * @method static \Omega\Cron\ScheduleTime   call(\Closure $call_back, array $params = [])
 * @method static void                        execute()
 * @method static void                        setLogger(\Omega\Cron\Schedule\InterpolateInterface $logger)
 * @method static void                        setTime(int $time)
 * @method static \Omega\Cron\Schedule       add(\Omega\Cron\Schedule $schedule)
 * @method static void                        flush()
 */
final class Schedule extends Facade
{
    protected static function getAccessor()
    {
        return 'schedule';
    }
}
