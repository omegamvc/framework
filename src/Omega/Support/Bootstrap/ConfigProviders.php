<?php

declare(strict_types=1);

namespace Omega\Support\Bootstrap;

use Omega\Application\Application;
use Omega\Config\ConfigRepository;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;

use function date_default_timezone_set;
use function file_exists;
use function glob;

class ConfigProviders
{
    /**
     * @throws NotFoundException
     * @throws DependencyException
     * @throws InvalidDefinitionException
     */
    public function bootstrap(Application $app): void
    {
        $configPath = get_path('path.config');
        $config     = [];
        $hasCache   = false;

        if (file_exists($file = $app->getApplicationCachePath() . 'config.php')) {
            $config   = require $file;
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

        date_default_timezone_set(env('APP_TIMEZONE'));
    }
}
