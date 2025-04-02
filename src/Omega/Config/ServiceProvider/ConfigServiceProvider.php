<?php

/**
 * Part of Omega - Config Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Config\ServiceProvider;

use Omega\Application\Application;
use Omega\Config\Config;
use Omega\Container\Contracts\ServiceProvider\ServiceProviderInterface;
use Omega\Support\Path;

use function basename;
use function glob;

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
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function bind(Application $application): void
    {
        $application->alias('config', function () {
            $config = [];

            foreach (glob(Path::getPath('config', '/*.php')) as $file) {
                $configName = basename($file, '.php');
                $config[$configName] = require $file;
            }

            return new Config($config);
        });
    }
}
