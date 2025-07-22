<?php

/**
 * Part of Omega - Cron Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Cron;

use Closure;

/**
 * Manages and executes a pool of scheduled tasks.
 *
 * The Schedule class allows registration of multiple scheduled callbacks,
 * optionally with retry logic and centralized logging. Tasks are represented by
 * ScheduleTime instances, and execution can be triggered manually via `execute()`.
 *
 * @category  Omega
 * @package   Cron
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class Schedule
{
    /**
     * @var ScheduleTime[] List of scheduled tasks.
     */
    protected array $pools = [];

    /**
     * @param int|null $time Optional base timestamp used for scheduling.
     * @param InterpolateInterface|null $logger Optional logger for interpolated output.
     */
    public function __construct(
        protected ?int $time = null,
        private ?InterpolateInterface $logger = null
    ) {
    }

    /**
     * Returns all scheduled tasks in the pool.
     *
     * @return ScheduleTime[] The list of scheduled task instances.
     */
    public function getPools(): array
    {
        return $this->pools;
    }

    /**
     * Registers a new callback to be scheduled for execution.
     *
     * @param Closure $callBack The callback to execute.
     * @param array $params Optional parameters to pass to the callback.
     * @return ScheduleTime The scheduled task instance.
     */
    public function call(Closure $callBack, array $params = []): ScheduleTime
    {
        return $this->pools[] = new ScheduleTime($callBack, $params, $this->time);
    }

    /**
     * Executes all scheduled tasks in the pool.
     *
     * Each task is executed and retried based on its configured retry strategy.
     * If a logger is defined, execution output will be recorded.
     *
     * @return void
     */
    public function execute(): void
    {
        foreach ($this->pools as $cron) {
            $cron->setLogger($this->logger);
            do {
                $cron->exec();
            } while ($cron->retryAttempts() > 0);

            if ($cron->isRetry()) {
                $cron->exec();
            }
        }
    }

    /**
     * Sets the logger instance for interpolated output during execution.
     *
     * @param InterpolateInterface $logger The logger to use.
     * @return void
     */
    public function setLogger(InterpolateInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Sets a custom time reference for scheduled tasks.
     *
     * @param int $time The timestamp to assign.
     * @return void
     */
    public function setTime(int $time): void
    {
        $this->time = $time;
    }

    /**
     * Merges another Schedule instance into the current pool.
     *
     * @param Schedule $schedule The schedule to merge.
     * @return $this
     */
    public function add(Schedule $schedule): self
    {
        foreach ($schedule->getPools() as $time) {
            $this->pools[] = $time;
        }

        return $this;
    }

    /**
     * Clears all scheduled tasks from the pool.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->pools = [];
    }
}
