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
use function basename;

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

        // Se esiste la cache, la carichiamo
        if (file_exists($file = $app->getApplicationCachePath() . 'config.php')) {
            $cachedConfig = require $file;

            if (!is_array($cachedConfig)) {
                throw new \RuntimeException(
                    "Invalid config cache file: expected array, got " . gettype($cachedConfig)
                );
            }

            $config   = $cachedConfig;
            $hasCache = true;
        }

        // Altrimenti leggiamo tutti i file di config
        if (!$hasCache) {
            foreach (glob($configPath . "*.php") as $path) {
                $key = basename($path, '.php'); // nome file come chiave principale
                $value = require $path;

                if (!is_array($value)) {
                    throw new \RuntimeException(
                        "Invalid config file [$path]: expected array, got " . gettype($value)
                    );
                }

                $config[$key] = $value;
            }
        }

        // Carichiamo il repository nel container/app
        $app->loadConfig(new ConfigRepository($config));

        // Impostiamo il timezone
        date_default_timezone_set(env('APP_TIMEZONE'));
    }
}
