<?php

/**
 * Part of Omega -  Session Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Session\ServiceProvider;

use Omega\Application\Application;
use Omega\Container\Contracts\ServiceProvider\ServiceProviderInterface;
use Omega\Support\Facade\Facades\Config;
use Omega\Session\Factory\SessionFactory;

/**
 * SessionServiceProvider class.
 *
 * The `SessionServiceProvider` class is responsible for creating the SessionFactory instance
 * and defining the available drivers for the session service, such as the 'native' driver.
 *
 * @category   Omega
 * @package    Session
 * @subpackage ServiceProvider
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class SessionServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function bind(Application $application): void
    {
        $application->alias('session', function () {
            $config  = Config::get('session');
            $default = $config['default'];

            return (new SessionFactory())->create($config[$default]);
        });
    }
}
