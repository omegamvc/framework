<?php

declare(strict_types=1);

namespace Omega\Console\Commands;

use Omega\Application\Application;
use Omega\Cache\CacheManager;
use Omega\Cache\Exceptions\UnknownStorageDriverException;
use Omega\Console\AbstractCommand;
use Omega\Console\Traits\CommandTrait;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;

use function array_keys;
use function is_array;
use function Omega\Console\fail;
use function Omega\Console\info;
use function Omega\Console\ok;

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
     * @throws InvalidDefinitionException
     * @throws UnknownStorageDriverException
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    public function clear(Application $app): int
    {
        if (false === $app->has('cache')) {
            fail('Cache is not set yet.')->out();

            return 1;
        }

        /** @var CacheManager|null $cache */
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
            $cache->driver()->clear();
            ok('Done default cache driver has been clear.')->out(false);

            return 0;
        }

        foreach ($drivers as $driver) {
            $cache->driver($driver)->clear();
            info("clear '$driver' driver.")->out(false);
        }

        return 0;
    }
}
