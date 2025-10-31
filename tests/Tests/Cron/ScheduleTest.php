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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\Cron\InterpolateInterface;
use Omega\Cron\Schedule;
use Omega\Time\Now;

use function ob_get_clean;
use function ob_start;
use function str_repeat;

/**
 * Class ScheduleTest
 *
 * This test suite verifies the behavior of the scheduling system when executing tasks,
 * particularly in scenarios involving errors, retries, logging, and conditional execution.
 *
 * The tests focus on the following behaviors:
 * - Ensuring that schedule execution continues even if a scheduled job throws an exception.
 * - Verifying retry logic, both with a fixed retry count and with conditional retry rules.
 * - Confirming that logging is triggered the correct number of times during repeated failures.
 * - Ensuring scheduled tasks can be conditionally skipped based on user-defined predicates.
 *
 * A mock logger implementing `InterpolateInterface` is injected to simulate log output without
 * performing any actual I/O operations. Output buffering is used to capture execution output
 * for assertions without affecting the test runner’s output.
 *
 * @category  Tests
 * @package   Cron
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Now::class)]
#[CoversClass(Schedule::class)]
final class ScheduleTest extends TestCase
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
