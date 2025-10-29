<?php

declare(strict_types=1);

namespace Tests\Cron;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\Cron\InterpolateInterface;
use Omega\Cron\Schedule;
use Omega\Cron\ScheduleTime;
use Omega\Time\Now;

use function Omega\Time\now;

#[CoversClass(Schedule::class)]
#[CoversClass(ScheduleTime::class)]
#[CoversClass(Now::class)]
final class CronTimeTest extends TestCase
{
    /** @var InterpolateInterface|null  */
    private ?InterpolateInterface $logger;

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->logger = null;
    }

    /**
     * Test it run only just in time.
     *
     * @return void
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testItRunOnlyJustInTime(): void
    {
        $anonymously = new Schedule(now()->timestamp, $this->logger);
        $anonymously
            ->call(fn (): string => 'due time')
            ->justInTime()
            ->eventName('test 01');

        foreach ($anonymously->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * Test i t run only every ten minute.
     *
     * @return void
     */
    public function testItRunOnlyEveryTenMinute(): void
    {
        $timeTravel  = new Now('09/07/2021 00:00:00');
        $anonymously = new Schedule($timeTravel->timestamp, $this->logger);
        $anonymously
            ->call(fn (): string => 'due time')
            ->everyTenMinute()
            ->eventName('test 10 minute');

        foreach ($anonymously->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * Test it run only every thirty minutes.
     *
     * @return void
     */
    public function testItRunOnlyEveryThirtyMinutes(): void
    {
        $timeTravel  = new Now('09/07/2021 00:30:00');
        $anonymously = new Schedule($timeTravel->timestamp, $this->logger);
        $anonymously
            ->call(fn (): string => 'due time')
            ->everyThirtyMinutes()
            ->eventName('test 30 minute');

        foreach ($anonymously->getPools() as $scheduleItem) {
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
        $timeTravel  = new Now('09/07/2021 02:00:00');
        $anonymously = new Schedule($timeTravel->timestamp, $this->logger);
        $anonymously
            ->call(fn (): string => 'due time')
            ->everyTwoHour()
            ->eventName('test 2 hour');

        foreach ($anonymously->getPools() as $scheduleItem) {
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
        $timeTravel  = new Now('09/07/2021 12:00:00');
        $anonymously = new Schedule($timeTravel->timestamp, $this->logger);
        $anonymously
            ->call(fn (): string => 'due time')
            ->everyTwelveHour()
            ->eventName('test 12 hour');

        foreach ($anonymously->getPools() as $scheduleItem) {
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
        $timeTravel  = new Now('09/07/2021 00:00:00');
        $anonymously = new Schedule($timeTravel->timestamp, $this->logger);
        $anonymously
            ->call(fn (): string => 'due time')
            ->hourly()
            ->eventName('test hourly');

        foreach ($anonymously->getPools() as $scheduleItem) {
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
        $timeTravel  = new Now('09/07/2021 05:00:00');
        $anonymously = new Schedule($timeTravel->timestamp, $this->logger);
        $anonymously
            ->call(fn (): string => 'due time')
            ->hourlyAt(5)
            ->eventName('test hourlyAt 5 hour');

        foreach ($anonymously->getPools() as $scheduleItem) {
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
        $timeTravel  = new Now('00:00:00');
        $anonymously = new Schedule($timeTravel->timestamp, $this->logger);
        $anonymously
            ->call(fn (): string => 'due time')
            ->daily()
            ->eventName('test daily');

        foreach ($anonymously->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                // die(var_dump($scheduleItem->getTimeExpect()));
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
        $timeTravel  = new Now('12/12/2012 00:00:00');
        $anonymously = new Schedule($timeTravel->timestamp, $this->logger);
        $anonymously
            ->call(fn (): string => 'due time')
            ->dailyAt(12)
            ->eventName('test dailyAt 12');

        foreach ($anonymously->getPools() as $scheduleItem) {
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
        $timeTravel  = new Now('12/16/2012 00:00:00');
        $anonymously = new Schedule($timeTravel->timestamp, $this->logger);
        $anonymously
            ->call(fn (): string => 'due time')
            ->weekly()
            ->eventName('test weekly');

        foreach ($anonymously->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                // die(var_dump($scheduleItem->getTimeExpect()));
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
        $timeTravel  = new Now('1/1/2012 00:00:00');
        $anonymously = new Schedule($timeTravel->timestamp, $this->logger);
        $anonymously
            ->call(fn (): string => 'due time')
            ->mountly()
            ->eventName('test monthly');

        foreach ($anonymously->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }
}
