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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Time\now;

/**
 * Unit tests for the basic scheduling functionality of the Schedule class.
 *
 * This test suite verifies that:
 * - Schedules are created correctly
 * - Anonymous execution is supported
 * - Multiple schedules can be added and flushed
 * - All scheduled tasks are properly wrapped in ScheduleTime instances
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
class BasicCronTest extends TestCase
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
     * Creates a sample schedule with a just-in-time task for testing purposes.
     *
     * This method returns a Schedule instance containing a single
     * scheduled callback to simulate task execution.
     *
     * @return Schedule
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
     * @retun void
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
     */
    public function testItScheduleRunAnonymously(): void
    {
        $anonymously = new Schedule(now()->timestamp, $this->logger);
        $anonymously
            ->call(fn (): string => 'is run anonymously')
            ->justInTime()
            ->setEventName('test 01')
            ->anonymously();

        $anonymously
            ->call(fn (): string => 'is run anonymously')
            ->hourly()
            ->setEventName('test 02')
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
     */
    public function testItCanAddSchedule(): void
    {
        $cron1 = new Schedule(now()->timestamp, $this->logger);
        $cron1->call(fn (): bool => true)->setEventName('from1');
        $cron2 = new Schedule(now()->timestamp, $this->logger);
        $cron2->call(fn (): bool => true)->setEventName('from2');
        $cron1->add($cron2);

        $this->assertEquals('from1', $cron1->getPools()[0]->getEventName());
        $this->assertEquals('from2', $cron1->getPools()[1]->getEventName());
    }

    /**
     * Test it can flush.
     *
     * @return void
     */
    public function testItCanFlush(): void
    {
        $cron = new Schedule(now()->timestamp, $this->logger);
        $cron->call(fn (): bool => true)->setEventName('one');
        $cron->call(fn (): bool => true)->setEventName('two');

        $this->assertCount(2, $cron->getPools());
        $cron->flush();
        $this->assertCount(0, $cron->getPools());
    }
}
