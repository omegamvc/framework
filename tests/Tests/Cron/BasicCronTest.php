<?php

/**
 * Part of Omega - Tests\Cron Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

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

/**
 * Tests core scheduling behavior for the basic cron system.
 *
 * This test suite ensures that the `Schedule` class and its related
 * `ScheduleTime` entries behave correctly when defining scheduled tasks.
 * It verifies that:
 *
 * - Schedule entries are properly instantiated and stored.
 * - Tasks can be executed anonymously when configured.
 * - Multiple schedule instances can be combined via `add()`.
 * - The schedule pool can be reset using `flush()`.
 *
 * The logger used in these tests is a minimal `InterpolateInterface`
 * implementation that simulates log output without producing real I/O.
 *
 * @category  Tests
 * @package   Cron
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Schedule::class)]
#[CoversClass(ScheduleTime::class)]
final class BasicCronTest extends TestCase
{
    /**
     * Logger instance used to handle interpolated debug output during schedule execution.
     *
     * This is a nullable implementation of `InterpolateInterface`, injected in `setUp()`
     * to avoid external dependencies. The logger does not write to files or streams; it
     * simply simulates logging so the tests can verify behavior without performing I/O.
     *
     * @var InterpolateInterface|null
     */
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
                $this->assertTrue($scheduleItem->anonymously);
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

        $this->assertEquals('from1', $cron1->getPools()[0]->eventName);
        $this->assertEquals('from2', $cron1->getPools()[1]->eventName);
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
