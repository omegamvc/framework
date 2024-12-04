<?php

/**
 * Part of Omega -  Queue Package.
 *
 * @see       https://omegamvc.github.io
 *
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Queue\Adapter;

/*
 * @use
 */
use Closure;
use Omega\Queue\Job;

/**
 * Abstract queue adapter class.
 *
 * The `AbstractQueueAdapter` class provides a foundation for building specific queue
 * adapters by defining common methods that a queue system should support. Queue adapters
 * are responsible for pushing jobs onto the queue and shifting jobs off the queue for
 * processing.
 *
 * @category    Omega
 * @package     Queue
 * @subpackage  Adapter
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
abstract class AbstractQueueAdapter implements QueueAdapterInterface
{
    /**
     * AbstractQueueAdapter class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        // --> Empty constructor for future use.
    }

    /**
     * {@inheritdoc}
     *
     * @param Closure $closure   Holds the closure representing the job to be pushed onto the queue.
     * @param mixed   ...$params Holds additional parameters needed for the job.
     *
     * @return int|string Returns the job identifier or status code.
     */
    abstract public function push(Closure $closure, ...$params): int|string;

    /**
     * {@inheritdoc}
     *
     * @return ?Job Returns the next job to be processed, or null if the queue is empty.
     */
    abstract public function shift(): ?Job;
}
