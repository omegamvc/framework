<?php

/**
 * Part of Omega - Database Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Database\Factory;

use Omega\Database\Adapter\DatabaseAdapterInterface;
use Omega\Database\Adapter\MysqlAdapter;
use Omega\Database\Adapter\SqliteAdapter;
use Omega\Database\Exception\AdapterException;

/**
 * Database factory class.
 *
 * The `DatabaseFactory` class is responsible for registering and creating session
 * drivers based on configurations. It acts as a factory for different session
 * drivers and provides a flexible way to connect to various session systems.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Factory
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class DatabaseFactory implements DatabaseFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(?array $config = null): DatabaseAdapterInterface
    {
        if (! isset($config['type'])) {
            throw new AdapterException(
                'Type is not defined.'
            );
        }

        return match ($config['type']) {
            'mysql'  => new MysqlAdapter($config),
            'sqlite' => new SqliteAdapter($config),
            default  => throw new AdapterException('Unrecognised type.')
        };
    }
}
