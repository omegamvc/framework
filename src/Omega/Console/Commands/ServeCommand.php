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
use Omega\Console\Style\Alert;
use Omega\Console\Style\Style;
use Omega\Console\Traits\PrintHelpTrait;

use function gethostbyname;
use function gethostname;
use function shell_exec;

/**
 * Class ServeCommand
 *
 * Starts a PHP built-in web server on a specified port, optionally exposing it to the public network.
 *
 * This command is useful for quickly serving a PHP application during development.
 *
 * Usage example:
 * ```
 * // Serve locally on default port 8080
 * $command = new ServeCommand();
 * $command->main();
 *
 * // Serve on port 3000 and expose to public network
 * $command->port = 3000;
 * $command->expose = true;
 * $command->main();
 * ```
 *
 * CLI invocation pattern:
 * ```
 * serve [--port=8080] [--expose]
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
 * @property string $port
 * @property bool   $expose
 */
class ServeCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Command registration details.
     * Defines the command pattern, handler, and default option values.
     *
     * @var array<int, array<string, mixed>>
     *
     * @example
     * ```
     * ServeCommand::$command = [
     *   [
     *     'pattern' => 'serve',
     *     'fn'      => [ServeCommand::class, 'main'],
     *     'default' => ['port' => 8080, 'expose' => false],
     *   ],
     * ];
     * ```
     */
    public static array $command = [
        [
            'pattern' => 'serve',
            'fn'      => [ServeCommand::class, 'main'],
            'default' => [
                'port'   => 8080,
                'expose' => false,
            ],
        ],
    ];

    /**
     * Returns the help information describing the command, its options, and relations.
     *
     * @return array<string, array<string, string|string[]>>
     *
     * @example
     * ```
     * $help = $command->printHelp();
     * // [
     * //   'commands' => ['serve' => 'Serve server with port number (default 8080)'],
     * //   'options' => ['--port' => 'Serve with costume port', '--expose' => 'Make server run public network'],
     * //   'relation' => ['serve' => ['--port', '--expose']],
     * // ]
     * ```
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'serve' => 'Serve server with port number (default 8080)',
            ],
            'options'   => [
                '--port'   => 'Serve with costume port',
                '--expose' => 'Make server run public network',
            ],
            'relation'  => [
                'serve' => ['--port', '--expose'],
            ],
        ];
    }

    /**
     * Main method to run the PHP built-in server.
     * Displays local and optionally network URLs, then starts the server.
     *
     * @return void
     *
     * @example
     * ```
     * $command = new ServeCommand();
     * $command->port = 8080;
     * $command->expose = false;
     * $command->main();
     * ```
     */
    public function main(): void
    {
        $port    = $this->port;
        $localIP = gethostbyname(gethostname());

        $print = new Style('Server running add:');

        $print
            ->newLines()
            ->push('Local')->tabs()->push("http://localhost:$port")->textBlue();

        if ($this->expose) {
            $print->newLines()->push('Network')->tabs()->push("http://$localIP:$port")->textBlue();
        }

        $print
            ->newLines(2)
            ->push('ctrl+c to stop server')
            ->newLines()
            ->tap(Alert::render()->info('server running...'))
            ->out(false);

        $address = $this->expose ? '0.0.0.0' : '127.0.0.1';

        shell_exec("php -S $address:$port -t public/");
    }
}
