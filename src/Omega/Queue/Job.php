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

namespace Omega\Queue;

use Omega\Database\AbstractModel;
use Omega\Support\Facade\Facades\Config;

use function unserialize;

/**
 * Job class.
 *
 * The `Job` class represents a job that can be added to the queue. Each job is associated
 * with a closure and its parameters. When the job is processed, the closure is executed
 * with the provided parameters.
 *
 * @category    Omega
 * @package     Queue
 * @link        https://omegamvc.github.io
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
class Job extends AbstractModel
{
    /**
     * Get the database table associated with the model.
     *
     * @return string Return the database table.
     */
    public function getTable(): string
    {
        return Config::get('queue.database.table');
    }

    /**
     * Run the job.
     *
     * @return mixed Return the result of the closure execution.
     */
    public function run(): mixed
    {
        $closure = unserialize($this->closure);
        $params  = unserialize($this->params);

        return $closure(...$params);
    }
}
