<?php

/**
 * Part of Omega -  Session Package.
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

namespace Omega\Session\ServiceProvider;

/*
 * @use
 */
use Omega\Application\Application;
use Omega\Container\ServiceProvider\ServiceProviderInterface;
use Omega\Session\Factory\SessionFactory;
use Omega\Support\Facade\Facades\Config;

/**
 * SessionServiceProvider class.
 *
 * The `SessionServiceProvider` class is responsible for creating the SessionFactory instance
 * and defining the available drivers for the session service, such as the 'native' driver.
 *
 * @category    Omega
 * @package     Session
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
class SessionServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers or binds services into the application container.
     *
     * @param Application $application Holds the main application container to which services are bound.
     *
     * @return void This method does not return a value.
     */
    public function bind(Application $application): void
    {
 // Non deve ritornare un factory
        $application->alias('session', function () {
            $config  = Config::get('session');
            $default = $config['default'];

            return (new SessionFactory())->create($config[$default]);
        });
    }
}
