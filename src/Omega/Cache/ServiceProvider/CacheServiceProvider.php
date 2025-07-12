<?php

/**
 * Part of Omega - Cache Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Cache\ServiceProvider;

use Omega\Application\Application;
use Omega\Cache\Factory\CacheFactory;
use Omega\Container\Contracts\ServiceProvider\ServiceProviderInterface;
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
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
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
            /**@phpstan-ignore-next-line */
            $default = $config['default'];

            /**@phpstan-ignore-next-line */
            return (new CacheFactory())->create($config[$default]);
        });
    }
}
