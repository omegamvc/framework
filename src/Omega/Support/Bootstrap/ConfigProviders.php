<?php

declare(strict_types=1);

namespace Omega\Support\Bootstrap;

use Omega\Application\Application;
use Omega\Config\ConfigRepository;

use function array_merge;
use function date_default_timezone_set;
use function file_exists;
use function glob;

class ConfigProviders
{
    public function bootstrap(Application $app): void
    {
        $configPath = $app->getConfigPath();
        $config     =  $app->defaultConfigs();
        $hasCache   = false;

        if (file_exists($file = $app->getApplicationCachePath() . 'config.php')) {
            $config    = array_merge($config, require $file);
            $hasCache = true;
        }

        if (false === $hasCache) {
            foreach (glob($configPath . "*.php") as $path) {
                foreach (include $path as $key => $value) {
                    $config[$key] = $value;
                }
            }
        }

        $app->loadConfig(new ConfigRepository($config));

        date_default_timezone_set($config['time_zone'] ?? 'UTC');
    }
}
