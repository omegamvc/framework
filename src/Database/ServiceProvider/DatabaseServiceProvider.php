<?php

/**
 * Part of Omega -  Database Package.
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

namespace Omega\Database\ServiceProvider;

/*
 * @use
 */
use Omega\Application\Application;
use Omega\Container\ServiceProvider\ServiceProviderInterface;
use Omega\Database\Factory\DatabaseFactory;
use Omega\Support\Facade\Facades\Config;

/**
 * Database service provider class.
 *
 * The `DatabaseServiceProvider` class is responsible for creating the DatabaseFactory instance
 * and defining the available drivers for the session service, such as the 'native' driver.
 *
 * @category    Omega
 * @package     Database
 * @subpackage  ServiceProvider
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class DatabaseServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @param Application $application Holds the main application container to which services are bound.
     *
     * @return void This method does not return a value.
     */
    public function bind(Application $application): void
    {
 // Non deve ritornare un factory
        $application->alias('database', function () {
            $config  = Config::get('database');
            $default = $config['default'];

            return (new DatabaseFactory())->create($config[$default]);
        });
    }
}
