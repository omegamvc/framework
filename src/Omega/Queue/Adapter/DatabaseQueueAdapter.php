<?php

/**
 * Part of Omega -  Queue Package.
 * php version 8.3
 *
 * @link        https://omegamvc.github.io
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */

declare(strict_types=1);

namespace Omega\Queue\Adapter;

use Closure;
use Exception;
use Omega\Support\Facade\Facades\Config;
use Omega\SerializableClosure\SerializableClosure;
use Omega\Queue\Job;

use function serialize;

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
 * @link        https://omegamvc.github.io
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
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
     */
    public function shift(): ?Job
    {
        $attempts = Config::get('queue.database.attempts');

        return Job::where('attempts', '<', $attempts)
            ->where('is_complete', false)
            ->first();
    }
}
