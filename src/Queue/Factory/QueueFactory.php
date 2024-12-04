<?php

/**
 * Part of Omega - Queue Package.
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

namespace Omega\Queue\Factory;

/*
 * @use
 */
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
 * @package     Database
 * @subpackage  Factory
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class QueueFactory implements QueueFactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @return mixed Return the created object or value. The return type is flexible, allowing for any type to be
     *               returned, depending on the implementation.
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
