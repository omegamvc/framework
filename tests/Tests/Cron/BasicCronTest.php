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

use function Omega\Time\now;

#[CoversClass(Schedule::class)]
#[CoversClass(ScheduleTime::class)]
final class BasicCronTest extends TestCase
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
     * Sample schedule example.
     *
     * @return Schedule Return the Schedule object.
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    private function sampleSchedules(): Schedule
    {
        $schedule = new Schedule(now()->timestamp, $this->logger);
        $schedule
            ->call(fn (): string => 'test')
            ->justInTime();

        return $schedule;
    }

    /**
     * Test it correct schedule class.
     *
     * @return void
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testItCorrectScheduleClass(): void
    {
        foreach ($this->sampleSchedules()->getPools() as $scheduleItem) {
            $this->assertInstanceOf(ScheduleTime::class, $scheduleItem, 'this is schedule time');
        }
    }

    /**
     * Test it schedule run anonymously.
     *
     * @return void
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testItScheduleRunAnonymously(): void
    {
        $anonymously = new Schedule(now()->timestamp, $this->logger);
        $anonymously
            ->call(fn (): string => 'is run anonymously')
            ->justInTime()
            ->eventName('test 01')
            ->anonymously();

        $anonymously
            ->call(fn (): string => 'is run anonymously')
            ->hourly()
            ->eventName('test 02')
            ->anonymously();

        foreach ($anonymously->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isAnonymously());
            }
        }
    }

    /**
     * Test it can add schedule.
     *
     * @return void
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testItCanAddSchedule(): void
    {
        $cron1 = new Schedule(now()->timestamp, $this->logger);
        $cron1->call(fn (): bool => true)->eventName('from1');
        $cron2 = new Schedule(now()->timestamp, $this->logger);
        $cron2->call(fn (): bool => true)->eventName('from2');
        $cron1->add($cron2);

        $this->assertEquals('from1', $cron1->getPools()[0]->getEventname());
        $this->assertEquals('from2', $cron1->getPools()[1]->getEventname());
    }

    /**
     * Test it can flush.
     *
     * @return void
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testItCanFlush(): void
    {
        $cron = new Schedule(now()->timestamp, $this->logger);
        $cron->call(fn (): bool => true)->eventName('one');
        $cron->call(fn (): bool => true)->eventName('two');

        $this->assertCount(2, $cron->getPools());
        $cron->flush();
        $this->assertCount(0, $cron->getPools());
    }
}
