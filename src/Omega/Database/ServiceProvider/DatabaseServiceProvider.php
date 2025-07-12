<?php

/**
 * Part of Omega -  Database Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Database\ServiceProvider;

use Omega\Application\Application;
use Omega\Container\Contracts\ServiceProvider\ServiceProviderInterface;
use Omega\Database\Factory\DatabaseFactory;
use Omega\Support\Facade\Facades\Config;

/**
 * Database service provider class.
 *
 * The `DatabaseServiceProvider` class is responsible for creating the DatabaseFactory instance
 * and defining the available drivers for the session service, such as the 'native' driver.
 *
 * @category   Omega
 * @package    Database
 * @subpackage ServiceProvider
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class DatabaseServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function bind(Application $application): void
    {
        $application->alias('database', function () {
            $config  = Config::get('database');
            $default = $config['default'];

            return (new DatabaseFactory())->create($config[$default]);
        });

        //$application->dumpBindings();
    }
}
