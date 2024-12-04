<?php

/**
 * Part of Omega -  Logging Package.
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

namespace Omega\Logging\ServiceProvider;

/*
 * @use
 */
use Omega\Application\Application;
use Omega\Container\ServiceProvider\ServiceProviderInterface;
use Omega\Logging\Factory\LoggingFactory;
use Omega\Support\Facade\Facades\Config;

/**
 * Logging service provider class.
 *
 * The `LoggingServiceProvider` class is responsible for creating the LoggingFactory instance
 * and defining the available drivers for the logging service.
 *
 * @category    Omega
 * @package     Logging
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
class LoggingServiceProvider implements ServiceProviderInterface
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
        $application->alias('logging', function () {
            $config  = Config::get('logging');
            $default = $config['default'];

            return (new LoggingFactory())->create($config[$default]);
        });
    }
}
