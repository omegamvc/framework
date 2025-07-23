<?php

/**
 * Part of Omega - Tests\Cron Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Cron;

use Omega\Cron\InterpolateInterface;
use Omega\Cron\Schedule;
use Omega\Cron\ScheduleTime;
use Omega\Time\Now;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Time\now;

/**
 * Test suite for verifying the behavior of scheduled tasks using time-based constraints.
 *
 * This class contains unit tests for various scheduling rules, such as hourly, daily, and monthly executions.
 * Each test ensures that the schedule triggers the task only when the current time matches the defined condition.
 * A mock logger is used to test message interpolation during task execution.
 *
 * @category  Omega\Tests
 * @package   Cron
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Schedule::class)]
#[CoversClass(ScheduleTime::class)]
class CronTimeTest extends TestCase
{
    /** @var InterpolateInterface|null Logger implementation used for testing interpolation within the schedule. */
    private ?InterpolateInterface $logger;

    /**
     * Set up the test environment before each test.
     *
     * This method is called before each test method is run.
     * Override it to initialize objects, mock dependencies, or reset state.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->logger = new class implements InterpolateInterface {
            public function interpolate(string $message, array $context = []): void
            {
                echo 'works';
            }
        };
    }

    /**
     * Clean up the test environment after each test.
     *
     * This method flushes and resets the application container
     * to ensure a clean state between tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->logger = null;
    }

    /**
     * est it run only just in time.
     *
     * @return void
     */
    public function testItRunOnlyJustInTime(): void
    {
        $task = new Schedule(now()->timestamp, $this->logger);
        $task
            ->call(fn (): string => 'due time')
            ->justInTime()
            ->setEventName('test 01');

        foreach ($task->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * Test it run only every ten minute.
     *
     * @return void
     */
    public function testItRunOnlyEveryTenMinute(): void
    {
        $timeTravel = new Now('09/07/2021 00:00:00');
        $task       = new Schedule($timeTravel->timestamp, $this->logger);
        $task
            ->call(fn (): string => 'due time')
            ->everyTenMinute()
            ->setEventName('test 10 minute');

        foreach ($task->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * Test it run only every thirty minutes
     *
     * @return void
     */
    public function testItRunOnlyEveryThirtyMinutes(): void
    {
        $timeTravel = new Now('09/07/2021 00:30:00');
        $task       = new Schedule($timeTravel->timestamp, $this->logger);
        $task
            ->call(fn (): string => 'due time')
            ->everyThirtyMinutes()
            ->setEventName('test 30 minute');

        foreach ($task->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * Test it run only every two hour.
     *
     * @return void
     */
    public function testItRunOnlyEveryTwoHour(): void
    {
        $timeTravel = new Now('09/07/2021 02:00:00');
        $task       = new Schedule($timeTravel->timestamp, $this->logger);
        $task
            ->call(fn (): string => 'due time')
            ->everyTwoHour()
            ->setEventName('test 2 hour');

        foreach ($task->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * Test it run only every twelve hour.
     *
     * @return void
     */
    public function testItRunOnlyEveryTwelveHour(): void
    {
        $timeTravel = new Now('09/07/2021 12:00:00');
        $task       = new Schedule($timeTravel->timestamp, $this->logger);
        $task
            ->call(fn (): string => 'due time')
            ->everyTwelveHour()
            ->setEventName('test 12 hour');

        foreach ($task->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * Test it run only hourly.
     *
     * @return void
     */
    public function testItRunOnlyHourly(): void
    {
        $timeTravel = new Now('09/07/2021 00:00:00');
        $task       = new Schedule($timeTravel->timestamp, $this->logger);
        $task
            ->call(fn (): string => 'due time')
            ->hourly()
            ->setEventName('test hourly');

        foreach ($task->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * Test it run only hourly at.
     *
     * @return void
     */
    public function testItRunOnlyHourlyAt(): void
    {
        $timeTravel = new Now('09/07/2021 05:00:00');
        $task       = new Schedule($timeTravel->timestamp, $this->logger);
        $task
            ->call(fn (): string => 'due time')
            ->hourlyAt(5)
            ->setEventName('test hourlyAt 5 hour');

        foreach ($task->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * Test it run only daily.
     *
     * @return void
     */
    public function testItRunOnlyDaily(): void
    {
        $timeTravel = new Now('00:00:00');
        $task       = new Schedule($timeTravel->timestamp, $this->logger);
        $task
            ->call(fn (): string => 'due time')
            ->daily()
            ->setEventName('test daily');

        foreach ($task->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                // die(var_dump($scheduleItem->getTimeExact()));
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * Test it run only daily at.
     *
     * @return void
     */
    public function testItRunOnlyDailyAt(): void
    {
        $timeTravel = new Now('12/12/2012 00:00:00');
        $task       = new Schedule($timeTravel->timestamp, $this->logger);
        $task
            ->call(fn (): string => 'due time')
            ->dailyAt(12)
            ->setEventName('test dailyAt 12');

        foreach ($task->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * Test it run only weekly.
     *
     * @return void
     */
    public function testItRunOnlyWeekly(): void
    {
        $timeTravel = new Now('12/16/2012 00:00:00');
        $task       = new Schedule($timeTravel->timestamp, $this->logger);
        $task
            ->call(fn (): string => 'due time')
            ->weekly()
            ->setEventName('test weekly');

        foreach ($task->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                // die(var_dump($scheduleItem->getTimeExact()));
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * Test it run only monthly.
     *
     * @return void
     */
    public function testItRunOnlyMonthly(): void
    {
        $timeTravel = new Now('1/1/2012 00:00:00');
        $task       = new Schedule($timeTravel->timestamp, $this->logger);
        $task
            ->call(fn (): string => 'due time')
            ->monthly()
            ->setEventName('test monthly');

        foreach ($task->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }
}
