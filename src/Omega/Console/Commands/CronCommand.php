<?php

declare(strict_types=1);

namespace Omega\Console\Commands;

use Omega\Console\AbstractCommand;
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

class CronCommand extends AbstractCommand
{
    protected InterpolateInterface $log;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'cron',
            'fn'      => [self::class, 'main'],
        ], [
            'pattern' => 'cron:list',
            'fn'      => [self::class, 'list'],
        ], [
            'pattern' => 'cron:work',
            'fn'      => [self::class, 'work'],
        ],
    ];

    /**
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

    public function list(): int
    {
        $watchStart = microtime(true);
        $print      = new Style("\n");

        $info = [];
        $max  = 0;
        foreach ($this->getSchedule()->getPools() as $cron) {
            $time   = $cron->getTimeName();
            $name   = $cron->getEventname();
            $info[] = [
                'time'   => $time,
                'name'   => $name,
                'animus' => $cron->isAnonymously(),
            ];
            $max = max(strlen($time), $max);
        }
        foreach ($info as $cron) {
            $print->push('#');
            if ($cron['animus']) {
                $print->push($cron['time'])->textDim()->repeat(' ', $max + 1 - strlen($cron['time']));
            } else {
                $print->push($cron['time'])->textGreen()->repeat(' ', $max + 1 - strlen($cron['time']));
            }
            $print->push($cron['name'])->textYellow()->newLines();
        }

        $watchEnd = round(microtime(true) - $watchStart, 3) * 1000;
        $print->newLines()->push('done in ')
            ->push($watchEnd . ' ms')->textGreen()
            ->out();

        return 0;
    }

    public function work(): void
    {
        $print = new Style("\n");
        $print
            ->push('Simulate Cron in terminal (every minute)')->textBlue()
            ->newLines(2)
            ->push('type ctrl+c to stop')->textGreen()->underline()
            ->out();

        $terminalWidth = $this->getWidth(34, 50);

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
                ->repeat(' ', $terminalWidth - $print->length())
                ->push('-> ')->textDim()
                ->push($watchEnd . 'ms')->textYellow()
                ->out()
            ;

            // reset every 60 seconds
            sleep(60);
        }
    }

    /**
     * @return Schedule
     */
    protected function getSchedule(): Schedule
    {
        $schedule = Scheduler::add(new Schedule());
        $this->scheduler($schedule);

        return $schedule;
    }

    /**
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
        ->eventName('cli-schedule');

        // others schedule
    }
}
