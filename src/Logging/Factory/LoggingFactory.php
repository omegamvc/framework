<?php

/**
 * Part of Omega - Logging Package.
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

namespace Omega\Logging\Factory;

/*
 * @use
 */
use Exception;
use Omega\Logging\LoggerInterface;
use Omega\Logging\Logger;

/**
 * Logging factory class.
 *
 * The `LoggingFactory` class is responsible for registering and creating session
 * drivers based on configurations. It acts as a factory for different logging
 * system and provides a flexible way to connect to various logger engines.
 *
 * @category    Omega
 * @package     Logging
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
class LoggingFactory implements LoggingFactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @return mixed Return the created object or value. The return type is flexible, allowing for any type to be
     *               returned, depending on the implementation.
     */
    public function create(?array $config = null): LoggerInterface
    {
        if (! isset($config['type'])) {
            throw new Exception(
                'Type is not defined.'
            );
        }

        return match ($config['type']) {
            'stream' => new Logger($config['path']),
            default  => throw new Exception('Unrecognized type.')
        };
    }
}
