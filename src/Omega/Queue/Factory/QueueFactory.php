<?php

/**
 * Part of Omega - Queue Package.
 * php version 8.3
 *
 * @link        https://omegamvc.github.io
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */

declare(strict_types=1);

namespace Omega\Queue\Factory;

use Exception;
use Omega\Queue\Adapter\DatabaseQueueAdapter;
use Omega\Queue\Adapter\QueueAdapterInterface;
use Omega\Queue\Exception\QueueException;

/**
 * Database factory class.
 *
 * The `DatabaseFactory` class is responsible for registering and creating session
 * drivers based on configurations. It acts as a factory for different session
 * drivers and provides a flexible way to connect to various session systems.
 *
 * @category    Omega
 * @package     Queue
 * @subpackage  Factory
 * @link        https://omegamvc.github.io
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
class QueueFactory implements QueueFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(?array $config = null): QueueAdapterInterface
    {
        if (! isset($config['type'])) {
            throw new Exception(
                'Type is not defined.'
            );
        }

        return match ($config['type']) {
            'database' => new DatabaseQueueAdapter($config),
            default    => throw new QueueException('Unrecognized type.')
        };
    }
}
