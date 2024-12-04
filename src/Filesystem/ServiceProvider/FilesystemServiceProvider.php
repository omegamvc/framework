<?php

/**
 * Part of Omega -  Filesystem Package.
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

namespace Omega\Filesystem\ServiceProvider;

/*
 * @use
 */
use Omega\Application\Application;
use Omega\Container\ServiceProvider\ServiceProviderInterface;
use Omega\Filesystem\Factory\FilesystemFactory;
use Omega\Support\Facade\Facades\Config;

/**
 * Filesystem service provider class.
 *
 * The `FilesystemServiceProvider` class is responsible for creating the FilesystemFactory instance
 * and defining the available drivers for the filesystem service, such as the 'local' driver.
 *
 * @category    Omega
 * @package     Filesystem
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
class FilesystemServiceProvider implements ServiceProviderInterface
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
        $application->alias('filesystem', function () {
            $config  = Config::get('filesystem');
            $default = $config['default'];

            return (new FilesystemFactory())->create($config[$default]);
        });
    }
}
