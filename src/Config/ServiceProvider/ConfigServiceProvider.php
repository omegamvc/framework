<?php

/**
 * Part of Omega - Config Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Config\ServiceProvider;

use Omega\Application\Application;
use Omega\Config\Config;
use Omega\Container\ServiceProvider\ServiceProviderInterface;

/**
 * Config service provider class.
 *
 * The `ConfigServiceProvider` class provides a service binding for the `Config` class
 * within the Omega framework. It allows you to easily access configuration parameters
 * throughout your application.
 *
 * @category   Omega
 * @package    Config
 * @subpackage ServiceProvider
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * Bind the configuration.
     *
     * Binds an instance of the `Config` class to the application container, allowing you
     * to resolve it using the `config` key.
     *
     * @param Application $application Holds an instance of Application.
     *
     * @return void
     */
    public function bind(Application $application): void
    {
        $application->alias('config', function () {
            return new Config();
        });
    }
}
