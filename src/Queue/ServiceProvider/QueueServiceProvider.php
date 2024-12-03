<?php

/**
 * Part of Omega CMS -  Queue Package.
 *
 * @see       https://omegacms.github.io
 *
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Queue\ServiceProvider;

/*
 * @use
 */
use Omega\Application\Application;
use Omega\Container\ServiceProvider\ServiceProviderInterface;
use Omega\Queue\Factory\QueueFactory;
use Omega\Support\Facade\Facades\Config;

/**
 * Queue service provider class.
 *
 * The `QueueServiceProvider` class is responsible for creating the QueueFactory instance
 * and defining the available drivers for the session service, such as the 'native' driver.
 *
 * @category    Omega
 * @package     Queue
 * @subpackage  ServiceProvider
 *
 * @see        https://omegacms.github.io
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class QueueServiceProvider implements ServiceProviderInterface
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
        $application->alias('queue', function () {
            $config  = Config::get('queue');
            $default = $config['default'];

            return (new QueueFactory())->create($config[$default]);
        });
    }
}
