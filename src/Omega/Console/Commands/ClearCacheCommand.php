<?php

declare(strict_types=1);

namespace Omega\Console\Commands;

use Omega\Console\Command;
use Omega\Console\Traits\CommandTrait;
use Omega\Application\Application;
use Omega\Template\Property;

use function Omega\Console\fail;
use function Omega\Console\info;
use function Omega\Console\ok;

/**
 * @property bool $update
 * @property bool $force
 */
class ClearCacheCommand extends Command
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
    public function printHelp()
    {
        return [
            'commands'  => [
                'cache:clear' => 'Clear cache (default drive)',
            ],
            'options'   => [
                '--all'     => 'Clear all registered cache driver.',
                '--drivers' => 'Clear spesific driver name.',
            ],
            'relation'  => [
                'cache:clear' => ['--all', '--drivers'],
            ],
        ];
    }

    public function clear(Application $app): int
    {
        if (false === $app->has('cache')) {
            fail('Cache is not set yet.')->out();

            return 1;
        }

        /** @var \Omega\Cache\CacheManager|null */
        $cache = $app['cache'];

        /** @var string[]|null */
        $drivers = null;

        /** @var string[]|string|bool */
        $user_drivers = $this->option('drivers', false);

        if ($this->option('all', false) && false === $user_drivers) {
            $drivers = array_keys(
                (fn (): array => $this->{'driver'})->call($cache) // @phpstan-ignore-line
            );
        }

        if ($user_drivers) {
            $drivers = is_array($user_drivers) ? $user_drivers : [$user_drivers];
        }

        if (null === $drivers) {
            $cache->driver()->clear();
            ok('Done default cache driver has been clear.')->out(false);

            return 0;
        }

        foreach ($drivers as $driver) {
            $cache->driver($driver)->clear();
            info("clear '{$driver}' driver.")->out(false);
        }

        return 0;
    }
}
