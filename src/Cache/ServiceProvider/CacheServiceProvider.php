<?php

/**
 * Part of Omega CMS - Cache Package.
 * php version 8.3
 *
 * @link      https://omegacms.github.io
 * @author    Adriano Giovannini <omegacms@outlook.com>
 * @copyright Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Cache\ServiceProvider;

use Omega\Application\Application;
use Omega\Cache\Factory\CacheFactory;
use Omega\Container\ServiceProvider\ServiceProviderInterface;
use Omega\Support\Facade\Facades\Config;

/**
 * Cache service provider class.
 *
 * The `CacheServiceProvider` class is responsible for providing cache-related
 * services to the framework. It defines the available cache drivers and their
 * factory methods.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage ServiceProvider
 * @link       https://omegacms.github.io
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class CacheServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function bind(Application $application): void
    {
        $application->alias('cache', function () {
            $config  = Config::get('cache');
            $default = $config['default'];

            return (new CacheFactory())->create($config[$default]);
        });
    }
}
