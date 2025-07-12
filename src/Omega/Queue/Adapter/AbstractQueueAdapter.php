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
 * @link        https://omegamvc.github.io
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
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
     */
    abstract public function push(Closure $closure, ...$params): int|string;

    /**
     * {@inheritdoc}
     */
    abstract public function shift(): ?Job;
}
