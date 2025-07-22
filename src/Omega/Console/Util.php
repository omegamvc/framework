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

namespace Omega\Console;

use Omega\Application\Application;
use Omega\Config\ConfigRepository;

/**
 * Class Util
 *
 * Provides utility functions for processing console command configurations.
 * Currently, it includes a static method to convert raw command definitions
 * from the application configuration into CommandMap instances.
 *
 * @category  Omega
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
final class Util
{
    /**
     * Load and convert command definitions from the configuration into CommandMap objects.
     *
     * This method retrieves the "commands" configuration from the application's ConfigRepository,
     * iterates through all defined command entries, and wraps each one in a CommandMap instance.
     *
     * @param Application $app The application container providing access to configuration services.
     * @return CommandMap[] An array of CommandMap instances created from the configuration.
     */
    public static function loadCommandFromConfig(Application $app): array
    {
        $commandMap = [];
        foreach ($app[ConfigRepository::class]->get('commands', []) as $commands) {
            foreach ($commands as $command) {
                $commandMap[] = new CommandMap($command);
            }
        }

        return $commandMap;
    }
}
