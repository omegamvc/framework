<?php

/**
 * Part of Omega CMS -  Queue Package.
 *
 * @see       https://omegacms.github.io
 *
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
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
use function serialize;
use Closure;
use Exception;
use Omega\SerializableClosure\SerializableClosure;
use Omega\Queue\Job;

/**
 * Database adapter class.
 *
 * The `DatabaseAdapter` class serves as a queue adapter for handling jobs using a database
 * backend. It allows pushing closures representing jobs to a queue and retrieving the next
 * job to be processed.
 * *
 * @category    Omega
 * @package     Queue
 * @subpackage  Adapter
 *
 * @see        https://omegacms.github.io
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class DatabaseQueueAdapter extends AbstractQueueAdapter
{
    /**
     * DatabaseAdapter class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        // --> Empty constructor for future use.
    }

    /**
     * {@inheritdoc}
     *
     * @param Closure $closure   Holds the closure representing the job to be pushed onto the queue.
     * @param mixed   ...$params Holds additional parameters needed for the job.
     *
     * @return int|string Returns the job identifier or status code.
     *
     * @throws Exception
     */
    public function push(Closure $closure, ...$params): int|string
    {
        $wrapper       = new SerializableClosure($closure);
        $job           = new Job();
        $job->closure  = serialize($wrapper);
        $job->params   = serialize($params);
        $job->attempts = 0;
        $job->save();

        return $job->id;
    }

    /**
     * {@inheritdoc}
     *
     * @return ?Job Returns the next job to be processed, or null if the queue is empty.
     */
    public function shift(): ?Job
    {
        $attempts = config('queue.database.attempts');

        return Job::where('attempts', '<', $attempts)
            ->where('is_complete', false)
            ->first();
    }
}
