<?php

/**
 * Part of Omega - Session Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Session\Factory;

use Omega\Session\Exception\StorageException;
use Omega\Session\Storage\NativeStorage;
use Omega\Session\Storage\StorageInterface;

/**
 * Cache factory class.
 *
 * The `SessionFactory` class is responsible for registering and creating session
 * drivers based on configurations. It acts as a factory for different session
 * drivers and provides a flexible way to connect to various session systems.
 *
 * @category   Omega
 * @package    Session
 * @subpackage Factory
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class SessionFactory implements SessionFactoryInterface
{
    /**
     * {@inheritdoc}
     * @return StorageInterface Return the created object or value. The return type is flexible, allowing for any type
     *                          to be returned, depending on the implementation.
     */
    public function create(?array $config = null): StorageInterface
    {
        if (!isset($config['type'])) {
            throw new StorageException(
                'Type is not defined.'
            );
        }

        return match ($config['type']) {
            'native' => new NativeStorage($config),
            default  => throw new StorageException('Unrecognized type.')
        };
    }
}
