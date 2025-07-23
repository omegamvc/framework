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

use DivisionByZeroError;
use Omega\Cron\InterpolateInterface;
use Omega\Cron\Schedule;
use Omega\Time\Now;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function ob_get_clean;
use function ob_start;
use function str_repeat;

/**
 * Unit tests for the Schedule class that manages time-based task execution.
 *
 * This test suite verifies the behavior of scheduled events, including:
 * - Execution flow when a job fails.
 * - Retry mechanisms (fixed and conditional).
 * - Logging through an interpolated logger.
 * - Conditional skipping of scheduled tasks.
 *
 * The tests use a custom mock of the InterpolateInterface to verify logging
 * behavior and ensure the schedule executes jobs correctly based on time and conditions.
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
class ScheduleTest extends TestCase
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
     * Test it can continue schedule event job fail.
     *
     * @return void
     */
    public function testItCanContinueScheduleEventJobFail(): void
    {
        $timeTravel = new Now('09/07/2021 00:30:00');
        $schedule   = new Schedule($timeTravel->timestamp, $this->logger);

        $schedule
            ->call(function () {
                throw new DivisionByZeroError("Intentional error for test");
            })
            ->everyThirtyMinutes()
            ->setEventName('test 30 minute');

        $schedule
            ->call(function () {
                $this->assertTrue(true);
            })
            ->everyTenMinute()
            ->setEventName('test 10 minute');

        ob_start();
        $schedule->execute();
        ob_get_clean();
    }

    /**
     * Test it can run retry schedule.
     *
     * @return void
     */
    public function testItCanRunRetrySchedule(): void
    {
        $timeTravel = new Now('09/07/2021 00:30:00');
        $schedule   = new Schedule($timeTravel->timestamp, $this->logger);

        $schedule
            ->call(function () {
                throw new DivisionByZeroError("Intentional error for test");
            })
            ->retry(5)
            ->everyThirtyMinutes()
            ->setEventName('test 30 minute');

        $schedule
            ->call(function () {
                $this->assertTrue(true);
            })
            ->everyTenMinute()
            ->setEventName('test 10 minute');

        ob_start();
        $schedule->execute();
        ob_get_clean();
    }

    /**
     * Test it can run retry condition schedule.
     *
     * @return void
     */
    public function testItCanRunRetryConditionSchedule(): void
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
            ->setEventName('test 30 minute');

        ob_start();
        $schedule->execute();
        ob_get_clean();
        $this->assertEquals(3, $test);
    }

    /**
     * Test it can log cron exact whenever condition.
     *
     * @return void
     */
    public function testItCanLogCronExactWheneverCondition(): void
    {
        $timeTravel = new Now('09/07/2021 00:30:00');
        $schedule   = new Schedule($timeTravel->timestamp, $this->logger);

        $schedule
            ->call(fn () => throw new DivisionByZeroError('Intentional error for test'))
            ->retry(20)
            ->everyThirtyMinutes()
            ->setEventName('test 30 minute');

        ob_start();
        $schedule->execute();
        $out = ob_get_clean();

        $this->assertEquals(str_repeat('works', 20), $out);
    }

    /**
     * Test it can skip schedule event is due.
     *
     * @return void
     */
    public function testItCanSkipScheduleEventIsDue(): void
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
