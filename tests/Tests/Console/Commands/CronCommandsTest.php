<?php

/**
 * Part of Omega - Tests\Console\Commands Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Console\Commands;

use Omega\Console\Commands\CronCommand;
use Omega\Cron\InterpolateInterface;
use Omega\Cron\Schedule;
use Omega\Support\Facades\Schedule as FacadesSchedule;
use PHPUnit\Framework\Attributes\CoversClass;

use function ob_get_clean;
use function ob_start;

/**
 * Class CronCommandsTest
 *
 * This class contains unit tests for the CronCommand functionality,
 * including main execution, listing registered commands, and facade integration.
 *
 * @category   Omega\Tests
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(CronCommand::class)]
class CronCommandsTest extends CommandTestHelper
{
    /**
     * @var int The current test time used to simulate scheduled tasks.
     */
    private int $time;

    /**
     * Set up the test environment before each test.
     *
     * Initializes the application with a custom Schedule instance
     * and binds it to the service container.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $log = new class implements InterpolateInterface {
            /**
             * Interpolates a message with the given context.
             *
             * @param string $message The message to interpolate.
             * @param array<string, mixed> $context The context for placeholders.
             *
             * @return void
             */
            public function interpolate(string $message, array $context = []): void
            {
            }
        };
        $this->time = 10;
        $this->app->set('schedule', fn () => new Schedule($this->time, $log));
        new FacadesSchedule($this->app);
    }

    /**
     * Tear down the test environment after each test.
     *
     * Flushes the FacadesSchedule to ensure test isolation.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        FacadesSchedule::flush();
    }

    /**
     * Create a customized CronCommand instance for testing.
     *
     * This method returns an anonymous subclass of CronCommand with a dummy logger.
     *
     * @return CronCommand The customized CronCommand instance.
     */
    private function maker(): CronCommand
    {
        return new class ($this->argv('omega cron')) extends CronCommand {
            public function __construct($argv)
            {
                parent::__construct($argv);
                $this->log = new class implements InterpolateInterface {
                    /**
                     * Interpolates a message with the given context.
                     *
                     * @param string $message The message to interpolate.
                     * @param array<string, mixed> $context The context for placeholders.
                     *
                     * @return void
                     */
                    public function interpolate(string $message, array $context = []): void
                    {
                    }
                };
            }
        };
    }

    /**
     * Test that the cron command main method executes successfully.
     *
     * @return void
     */
    public function testItCanCallCronCommandMain(): void
    {
        $cronCommand = $this->maker();
        ob_start();
        $exit = $cronCommand->main();
        ob_get_clean();

        $this->assertSuccess($exit);
    }

    /**
     * Test that the cron command list method executes successfully.
     *
     * @return void
     */
    public function testItCanCallCronCommandList(): void
    {
        $cronCommand = $this->maker();
        ob_start();
        $exit = $cronCommand->list();
        ob_get_clean();

        $this->assertSuccess($exit);
    }

    /**
     * Test that a scheduled event registered via the facade appears in the list.
     *
     * @return void
     */
    public function testItCanRegisterFromFacade(): void
    {
        FacadesSchedule::call(static fn (): int => 0)
            ->setEventName('from-static')
            ->justInTime();

        $cronCommand = $this->maker();
        ob_start();
        $exit = $cronCommand->list();
        $out  = ob_get_clean();

        $this->assertContain('from-static', $out);
        $this->assertContain('omega-schedule', $out);
        $this->assertSuccess($exit);
    }

    /**
     * Test that the schedule time configured matches the expected test value.
     *
     * @return void
     */
    public function testScheduleTimeMustEqual(): void
    {
        FacadesSchedule::call(static fn (): int => 0)
            ->setEventName('from-static')
            ->justInTime();

        $cronCommand = $this->maker();

        $schedule = (fn () => $this->{'getSchedule'}())->call($cronCommand);
        $time     = (fn () => $this->{'time'})->call($schedule);

        $this->assertEquals($this->time, $time);
    }
}
