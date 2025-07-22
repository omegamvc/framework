<?php

/**
 * Part of Omega - Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Console\Commands;

use Omega\Console\Command;
use Omega\Console\Style\Style;
use Omega\Cron\InterpolateInterface;
use Omega\Cron\Schedule;
use Omega\Support\Facades\Schedule as Scheduler;
use Omega\Time\Now;

use function max;
use function microtime;
use function Omega\Console\info;
use function round;
use function strlen;

/**
 * Console command for managing scheduled Cron jobs.
 *
 * This command provides three primary operations:
 *
 * It leverages the Omega Cron scheduling system and integrates with
 * the terminal UI via the Style component. Useful for debugging, monitoring,
 * and simulating cron behavior in a CLI environment.
 *
 * Example usage:
 * ```php
 * php omega cron      # Executes all scheduled cron tasks once.
 * php omega cron:list # Displays a list of registered scheduled events.
 * php omega cron:work # Simulates an always-running cron job in the terminal.
 * ```
 *
 * @category   Omega
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class CronCommand extends Command
{
    /** @var InterpolateInterface Logger instance used to store messages during cron execution. */
    protected InterpolateInterface $log;

    /**
     * Command registration definition.
     *
     * This array registers the CLI pattern `cache:clear` and associates it
     * with the `clear` method of this command class.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'cron',
            'fn'      => [self::class, 'main'],
        ],
        [
            'pattern' => 'cron:list',
            'fn'      => [self::class, 'list'],
        ],
        [
            'pattern' => 'cron:work',
            'fn'      => [self::class, 'work'],
        ],
    ];

    /**
     * Returns help metadata for the CLI command.
     *
     * This includes the command pattern, available options,
     * and option-command relationships used to display help information
     * to the user via `php omega list` or similar commands.
     *
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'cron'      => 'Run cron job (all schedule)',
                'cron:work' => 'Run virtual cron job in terminal (async)',
                'cron:list' => 'Get list of schedule',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    /**
     * Executes all scheduled Cron jobs once.
     *
     * This is the main entry point for the `cron` CLI command.
     * It measures execution time and prints the duration at the end.
     *
     * @return int 0 on success.
     */
    public function main(): int
    {
        $watchStart = microtime(true);

        $this->getSchedule()->execute();

        $watchEnd = round(microtime(true) - $watchStart, 3) * 1000;
        info('done in')
            ->push($watchEnd . 'ms')->textGreen()
            ->out();

        return 0;
    }

    /**
     * Lists all registered Cron jobs.
     *
     * Iterates through all scheduled tasks and prints their time slots,
     * names, and anonymity status. Useful for developers or sysadmins
     * to inspect the current cron configuration.
     *
     * @return int 0 on success.
     */
    public function list(): int
    {
        $watchStart = microtime(true);
        $print       = new Style("\n");

        $info = [];
        $max  = 0;
        foreach ($this->getSchedule()->getPools() as $cron) {
            $time   = $cron->getTimeName();
            $name   = $cron->getEventName();
            $info[] = [
                'time'        => $time,
                'name'        => $name,
                'anonymously' => $cron->isAnonymously(),
            ];
            $max = max(strlen($time), $max);
        }
        foreach ($info as $cron) {
            $print->push('#');
            if ($cron['anonymously']) {
                $print->push($cron['time'])->textDim()->repeat(' ', $max + 1 - strlen($cron['time']));
            } else {
                $print->push($cron['time'])->textGreen()->repeat(' ', $max + 1 - strlen($cron['time']));
            }
            $print->push($cron['name'])->textYellow()->newLines();
        }

        $watch_end = round(microtime(true) - $watchStart, 3) * 1000;
        $print->newLines()->push('done in ')
            ->push($watch_end . ' ms')->textGreen()
            ->out();

        return 0;
    }

    /**
     * Simulates a Cron worker in the terminal.
     *
     * This method runs an infinite loop that triggers all scheduled
     * tasks every 60 seconds. It is useful for local development or
     * environments without a native cron daemon.
     *
     * @return void
     */
    public function work(): void
    {
        $print = new Style("\n");
        $print
            ->push('Simulate Cron in terminal (every minute)')->textBlue()
            ->newLines(2)
            ->push('type ctrl+c to stop')->textGreen()->underline()
            ->out();

        $terminal_width = $this->getWidth(34, 50);

        /* @phpstan-ignore-next-line */
        while (true) {
            $clock = new Now();
            $print = new Style();
            $time  = $clock->year . '-' . $clock->month . '-' . $clock->day;

            $print
                ->push('Run cron at - ' . $time)->textDim()
                ->push(' ' . $clock->hour . ':' . $clock->minute . ':' . $clock->second);

            $watchStart = microtime(true);

            $this->getSchedule()->execute();

            $watchEnd = round(microtime(true) - $watchStart, 3) * 1000;
            $print
                ->repeat(' ', $terminal_width - $print->length())
                ->push('-> ')->textDim()
                ->push($watchEnd . 'ms')->textYellow()
                ->out()
            ;

            // reset every 60 seconds
            sleep(60);
        }
    }

    /**
     * Retrieves and initializes the Schedule instance.
     *
     * Registers internal scheduled tasks by calling the `scheduler()` method
     * and returns the resulting schedule object.
     *
     * @return Schedule
     */
    protected function getSchedule(): Schedule
    {
        $schedule = Scheduler::add(new Schedule());
        $this->scheduler($schedule);

        return $schedule;
    }

    /**
     * Registers scheduled tasks to the Cron Schedule.
     *
     * This method is intended to be overridden or extended with custom
     * schedule definitions. By default, it defines a basic anonymous task
     * with retry and Just-In-Time execution options.
     *
     * @param Schedule $schedule
     * @return void
     */
    public function scheduler(Schedule $schedule): void
    {
        $schedule->call(fn () => [
            'code' => 200,
        ])
        ->retry(2)
        ->justInTime()
        ->anonymously()
        ->setEventName('omega-schedule');

        // others schedule
    }
}
