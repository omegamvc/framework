<?php

declare(strict_types=1);

namespace Tests\Cron;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\Cron\InterpolateInterface;
use Omega\Cron\Schedule;
use Omega\Time\Now;

use function ob_get_clean;
use function ob_start;
use function str_repeat;

#[CoversClass(Now::class)]
#[CoversClass(Schedule::class)]
final class ScheduleTest extends TestCase
{
    /** @var InterpolateInterface|null */
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
     * Test it can continue schedule  event job fail.
     *
     * @return void
     */
    public function testItCanContinueScheduleEventJobFail()
    {
        $timeTravel = new Now('09/07/2021 00:30:00');
        $schedule   = new Schedule($timeTravel->timestamp, $this->logger);

        $schedule
            ->call(function () {
                /** @noinspection PhpDivisionByZeroInspection */
                /** @noinspection PhpUnusedLocalVariableInspection */
                $division = 0 / 0;
            })
            ->everyThirtyMinutes()
            ->eventName('test 30 minute');

        $schedule
            ->call(function () {
                $this->assertTrue(true);
            })
            ->everyTenMinute()
            ->eventName('test 10 minute');

        ob_start();
        $schedule->execute();
        ob_get_clean();
    }

    /**
     * Test it can run retry schedule.
     *
     * @return void
     */
    public function testItCanRunRetrySchedule()
    {
        $timeTravel = new Now('09/07/2021 00:30:00');
        $schedule   = new Schedule($timeTravel->timestamp, $this->logger);

        $schedule
            ->call(function () {
                /** @noinspection PhpDivisionByZeroInspection */
                /** @noinspection PhpUnusedLocalVariableInspection */
                $division = 0 / 0;
            })
            ->retry(5)
            ->everyThirtyMinutes()
            ->eventName('test 30 minute');

        $schedule
            ->call(function () {
                $this->assertTrue(true);
            })
            ->everyTenMinute()
            ->eventName('test 10 minute');

        ob_start();
        $schedule->execute();
        ob_get_clean();
    }

    /**
     * Test it can run retry condition schedule.
     *
     * @return void
     */
    public function testItCanRunRetryConditionSchedule()
    {
        $timeTravel = new Now('09/07/2021 00:30:00');
        $schedule   = new Schedule($timeTravel->timestamp, $this->logger);

        $test = 1;

        $schedule
            ->call(function () use (&$test) {
                $test++;
            })
            ->retryIf(true)
            ->everyThirtyMinutes()
            ->eventName('test 30 minute');

        ob_start();
        $schedule->execute();
        ob_get_clean();
        $this->assertEquals(3, $test);
    }

    /**
     * Tets it can log cron expect whatever condition.
     *
     * @return void
     */
    public function testItCanLogCronExpectWhateverCondition()
    {
        $timeTravel = new Now('09/07/2021 00:30:00');
        $schedule   = new Schedule($timeTravel->timestamp, $this->logger);

        $schedule
            ->call(function () {
                /** @noinspection PhpDivisionByZeroInspection */
                return 0 / 0;
            })
            ->retry(20)
            ->everyThirtyMinutes()
            ->eventName('test 30 minute');

        ob_start();
        $schedule->execute();
        $out = ob_get_clean();

        $this->assertEquals(str_repeat('works', 20), $out);
    }

    /**
     * est it can skip schedule event is due.
     *
     * @return void
     */
    public function testItCanSkipScheduleEventIsDue()
    {
        $timeTravel  = new Now('09/07/2021 00:30:00');
        $schedule    = new Schedule($timeTravel->timestamp, $this->logger);
        $alwaysFalse = false;

        $schedule
            ->call(function () use (&$alwaysFalse) {
                $alwaysFalse = true;

                return 'never call';
            })
            ->justInTime()
            ->skip(fn (): bool => true);

        $schedule->execute();
        $this->assertFalse($alwaysFalse);
    }
}
