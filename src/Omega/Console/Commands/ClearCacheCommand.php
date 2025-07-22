<?php

/**
 * Part of Omega - Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Console\Commands;

use Omega\Cache\AbstractCache;
use Omega\Console\Command;
use Omega\Console\Traits\CommandTrait;
use Omega\Application\Application;

use function array_keys;
use function is_array;
use function Omega\Console\fail;
use function Omega\Console\info;
use function Omega\Console\ok;

/**
 * Console command to clear cache drivers.
 *
 * This command provides a CLI interface to clear cache in an Omega application.
 * It supports clearing the default cache driver, all registered drivers,
 * or one or more specific drivers by name. It also validates the provided
 * driver names and handles invalid input gracefully.
 *
 * Example usage:
 * ```
 * php omega clear:cache               # Delete default data cache
 * php omega clear:cache --all         # Delete al cache
 * php omega clear:cache --drivers=xxx # Delete specific cache
 * ```
 * @category   Omega
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
 * @property bool $update
 * @property bool $force
 */
class ClearCacheCommand extends Command
{
    use CommandTrait;

    /**
     * Command registration definition.
     *
     * This array registers the CLI pattern `cache:clear` and associates it
     * with the `clear` method of this command class.
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
     * Returns help metadata for the CLI command.
     *
     * This includes the command pattern, available options,
     * and option-command relationships used to display help information
     * to the user via `php omega list` or similar commands.
     *
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
     * Clears the application cache.
     *
     * This method handles logic for:
     * - clearing the default cache driver,
     * - clearing all registered drivers (`--all`),
     * - clearing specific drivers by name (`--drivers`),
     * and performs validation on user input.
     *
     * It returns a status code: `0` on success, `1` on failure (e.g., unknown drivers).
     *
     * @param Application $app The application instance containing the cache service.
     * @return int The exit code of the command (0 = success, 1 = error).
     */
    public function clear(Application $app): int
    {
        if ($app->has('cache') === false) {
            fail('Cache is not set yet.')->out();
            return 1;
        }

        /** @var AbstractCache|null $cache */
        $cache = $app['cache'];

        /** @var string[]|null $drivers */
        $drivers = null;

        /** @var string[]|string|bool $userDrivers */
        $userDrivers = $this->option('drivers', false);

        $registeredDrivers = array_keys((fn (): array => $this->{'driver'})->call($cache));

        if ($this->option('all', false) && false === $userDrivers) {
            $drivers = $registeredDrivers;
        }

        if ($userDrivers) {
            $drivers = is_array($userDrivers) ? $userDrivers : [$userDrivers];

            $unknownDrivers = array_diff($drivers, $registeredDrivers);

            if (!empty($unknownDrivers)) {
                foreach ($unknownDrivers as $invalid) {
                    fail("Driver '$invalid' does not exist.")->out(false);
                }
                return 1;
            }
        }

        if (null === $drivers) {
            $cache->driver()->clear();
            ok('Done default cache driver has been cleared.')->out(false);
            return 0;
        }

        foreach ($drivers as $driver) {
            $cache->driver($driver)->clear();
            info("clear '$driver' driver.")->out(false);
        }

        return 0;
    }
}
