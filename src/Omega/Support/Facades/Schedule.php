<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\Cron\InterpolateInterface;

/**
 * @method static \Omega\Cron\ScheduleTime[] getPools()
 * @method static \Omega\Cron\ScheduleTime call(\Closure $call_back, array $params = [])
 * @method static void execute()
 * @method static void setLogger(InterpolateInterface $logger)
 * @method static void setTime(int $time)
 * @method static \Omega\Cron\Schedule add(\Omega\Cron\Schedule $schedule)
 * @method static void flush()
 */
final class Schedule extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'schedule';
    }
}
