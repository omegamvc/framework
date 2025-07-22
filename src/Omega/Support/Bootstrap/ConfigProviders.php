<?php

/**
 * Part of Omega - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Bootstrap;

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Application\Application;
use Omega\Config\ConfigRepository;

use function array_merge;
use function date_default_timezone_set;
use function file_exists;
use function glob;

/**
 * Class ConfigProviders
 *
 * Responsible for bootstrapping the application's configuration.
 * It loads default configuration values, merges them with either
 * cached or file-based configuration, and applies them to the application.
 *
 * Also sets the default timezone based on the loaded configuration.
 *
 * @category   Omega
 * @package    Support
 * @subpackage Bootstrap
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class ConfigProviders
{
    /**
     * Bootstraps the configuration for the given application instance.
     *
     * Loads default configuration values, then attempts to load and merge
     * either a cached configuration file (if available) or individual config
     * files from the config directory. Applies the resulting configuration
     * to the application and sets the default timezone.
     *
     * @param Application $app The application instance to bootstrap.
     * @return void
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required configuration or service is not found.
     */
    public function bootstrap(Application $app): void
    {
        $configPath = $app->getConfigPath();
        $config     = $app->defaultConfigs();
        $hasCache   = false;

        if (file_exists($file = $app->getApplicationCachePath() . '.php')) {
            $config    = array_merge($config, require $file);
            $hasCache = true;
        }

        if (false === $hasCache) {
            /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
            foreach (glob("{$configPath}*.php") as $path) {
                foreach (include $path as $key => $value) {
                    $config[$key] = $value;
                }
            }
        }

        $app->loadConfig(new ConfigRepository($config));

        date_default_timezone_set($config['time_zone'] ?? 'UTC');
    }
}
