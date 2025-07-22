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

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Application\Application;
use Omega\Config\ConfigRepository;
use Omega\Console\Command;
use Omega\Support\Bootstrap\ConfigProviders;

use function file_exists;
use function file_put_contents;
use function Omega\Console\fail;
use function Omega\Console\ok;
use function unlink;

/**
 * Handles application configuration caching and clearing via CLI.
 *
 * This command provides two operations:
 * - `config:cache`: Bootstraps the application, gathers all config values,
 *    and writes them to a single cache file.
 * - `config:clear`: Deletes the cached config file if it exists.
 *
 * This helps improve performance in production by avoiding repeated config loading.
 *
 * Example usage:
 * ```
 * php omega config:cache # Builds the configuration cache
 * php omega config:clear # Removes the cached configuration
 * ```
 *
 * @category   Omega
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class ConfigCommand extends Command
{
    /** @var array<int, array<string, mixed>> List of available command patterns and corresponding method handler */
    public static array $command = [
        [
            'pattern' => 'config:cache',
            'fn'      => [self::class, 'main'],
        ],
        [
            'pattern' => 'config:clear',
            'fn'      => [self::class, 'clear'],
        ],
    ];

    /**
     * Returns help information for the command-line interface.
     *
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'config:cache' => 'Build cache application config',
                'config:clear' => 'Remove cached application config',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    /**
     * Builds and writes the application configuration to a cached file.
     *
     * It first clears any existing config cache, then bootstraps the application
     * and serializes the configuration array into a file (`config.php`) located
     * in the application's cache path.
     *
     * @return int Exit code: 0 on success, 1 on failure
     * @throws DependencyException If a required dependency is missing
     * @throws NotFoundException If a required service is not found
     */
    public function main(): int
    {
        $app = Application::getInstance();
        (new ConfigProviders())->bootstrap($app);

        $this->clear();
        $config        = $app->get(ConfigRepository::class)->toArray();
        $cached_config = '<?php return ' . var_export($config, true) . ';' . PHP_EOL;
        if (file_put_contents($app->getApplicationCachePath() . 'config.php', $cached_config)) {
            ok('Config file has successfully created.')->out();

            return 0;
        }
        fail('Cant build config cache.')->out();

        return 1;
    }

    /**
     * Deletes the cached configuration file if it exists.
     *
     * This is typically called before generating a new config cache.
     *
     * @return int Exit code: 0 if the file was deleted, 1 if not found
     */
    public function clear(): int
    {
        if (file_exists($file = Application::getInstance()->getApplicationCachePath() . 'config.php')) {
            @unlink($file);
            ok('Clear config file has successfully.')->out();

            return 0;
        }

        return 1;
    }
}
