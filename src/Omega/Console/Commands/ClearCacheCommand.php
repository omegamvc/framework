<?php

declare(strict_types=1);

namespace Omega\Console\Commands;

use Omega\Application\Application;
use Omega\Cache\CacheFactory;
use Omega\Cache\Exceptions\UnknownStorageException;
use Omega\Console\AbstractCommand;
use Omega\Console\Traits\CommandTrait;
use Omega\Container\Exceptions\CircularAliasException;

use function array_keys;
use function is_array;
use function Omega\Console\error;
use function Omega\Console\info;
use function Omega\Console\success;

/**
 * @property bool $update
 * @property bool $force
 */
class ClearCacheCommand extends AbstractCommand
{
    use CommandTrait;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'cache:clear',
            'fn'      => [ClearCacheCommand::class, 'clear'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'cache:clear' => 'Clear cache (default drive)',
            ],
            'options'   => [
                '--all'     => 'Clear all registered cache driver.',
                '--drivers' => 'Clear specific driver name.',
            ],
            'relation'  => [
                'cache:clear' => ['--all', '--drivers'],
            ],
        ];
    }

    /**
     * @param Application $app
     * @return int
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws UnknownStorageException if a requested cache storage driver is unknown, unregistered, or unsupported.
     */
    public function clear(Application $app): int
    {
        if (false === $app->has('cache')) {
            error('Cache is not set yet.')->out();

            return 1;
        }

        /** @var CacheFactory|null $cache */
        $cache = $app['cache'];

        /** @var string[]|null $drivers */
        $drivers = null;

        /** @var string[]|string|bool $userDrivers */
        $userDrivers = $this->option('drivers', false);

        if ($this->option('all', false) && false === $userDrivers) {
            $drivers = array_keys(
                (fn (): array => $this->{'driver'})->call($cache)
            );
        }

        if ($userDrivers) {
            $drivers = is_array($userDrivers) ? $userDrivers : [$userDrivers];
        }

        if (null === $drivers) {
            $cache->getDriver()->clear();
            success('Done default cache driver has been clear.')->out(false);

            return 0;
        }

        foreach ($drivers as $driver) {
            $cache->getDriver($driver)->clear();
            info("Clear '$driver' driver.")->out(false);
        }

        return 0;
    }
}
