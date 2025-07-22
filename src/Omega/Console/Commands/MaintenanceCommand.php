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

use Omega\Console\Command;
use Omega\Console\Traits\PrintHelpTrait;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function Omega\Console\info;
use function Omega\Console\ok;
use function Omega\Console\warn;
use function unlink;

/**
 * Class MaintenanceCommand
 *
 * Provides CLI support to toggle the application’s maintenance mode.
 *
 * This command registers two subcommands:
 * - `down`: Activates maintenance mode by creating marker files in the storage path.
 * - `up`: Deactivates maintenance mode by removing those marker files.
 *
 * These commands are typically used during deployments or planned downtimes to prevent user access
 * while the application is being updated.
 *
 * When maintenance mode is active, the application can show a predefined message or page to visitors.
 *
 * ### Available Commands:
 * - `php omega maintenance:down` → Puts the application into maintenance mode.
 * - `php omega maintenance:up` → Brings the application back online.
 *
 * ### Example usage:
 * ```bash
 * # Enter maintenance mode
 * php omega maintenance:down
 *
 * # Exit maintenance mode
 * php omega maintenance:up
 * ```
 *
 * If the application is already in the target state (e.g., already down or up),
 * a warning is shown and the command exits with a non-zero status code.
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
class MaintenanceCommand extends Command
{
    use PrintHelpTrait;

    /**
     * The registered subcommands for this command.
     *
     * Each element maps a command pattern to a handler function.
     *
     * @var array<int, array{pattern: string, fn: array{class-string, string}}>
     */
    public static array $command = [
        [
            'pattern' => 'up',
            'fn'      => [self::class, 'up'],
        ],
        [
            'pattern' => 'down',
            'fn'      => [self::class, 'down'],
        ],
    ];

    /**
     * Returns help information for this command.
     *
     * @return array{
     *     commands: array<string, string>,
     *     options: array<string, string|string[]>,
     *     relation: array<string, string|string[]>
     * }
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'down' => 'Active maintenance mode',
                'up'   => 'Deactivate maintenance mode',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    /**
     * Puts the application into maintenance mode.
     *
     * Creates marker files under the storage directory.
     *
     * @return int 0 on success, 1 if already in maintenance mode
     */
    public function down(): int
    {
        if (app()->isDownMaintenanceMode()) {
            warn('Application is already under maintenance mode.')->out();

            return 1;
        }

        if (false === file_exists($down = storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down')) {
            file_put_contents($down, file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'down'));
        }

        file_put_contents(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'maintenance'));
        ok('Success, your application now in under maintenance.')->out();

        return 0;
    }

    /**
     * Brings the application back online by removing the maintenance file.
     *
     * @return int 0 on success, 1 if not in maintenance mode or failed to remove the file
     */
    public function up(): int
    {
        if (false === app()->isDownMaintenanceMode()) {
            warn('Application is not in maintenance mode.')->out();

            return 1;
        }

        if (false === unlink($up = storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php')) {
            warn('Application still maintenance mode.')->out(false);
            info("Remove manually maintenance file in `{$up}`.")->out();

            return 1;
        }

        ok('Success, your application now live.')->out();

        return 0;
    }
}
