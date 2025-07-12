<?php

/**
 * Part of Omega -  Queue Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Queue\Adapter;

use Closure;
use Omega\Queue\Job;

/**
 * Queue adapter interface.
 *
 * `QueueAdapterInterface` defines the contract for queue adapters in the
 * Omega Queue Package.
 *
 * @category    Omega
 * @package     Queue
 * @subpackage  Adapter
 * @link        https://omegamvc.github.io
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
interface QueueAdapterInterface
{
    /**
     * Push a job onto the queue.
     *
     * @param Closure $closure   Holds the closure representing the job to be pushed onto the queue.
     * @param mixed   ...$params Holds additional parameters needed for the job.
     * @return int|string Returns the job identifier or status code.
     */
    public function push(Closure $closure, ...$params): int|string;

    /**
     * Shift the next job off the queue.
     *
     * @return ?Job Returns the next job to be processed, or null if the queue is empty.
     */
    public function shift(): ?Job;
}
