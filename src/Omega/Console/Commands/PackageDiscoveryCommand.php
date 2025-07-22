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
use Omega\Console\Style\Style;
use Omega\Application\Application;
use Omega\Support\PackageManifest;
use Throwable;

use function array_keys;
use function Omega\Console\fail;
use function Omega\Console\info;
use function strlen;

/**
 * Command to trigger Composer package discovery and rebuild the package manifest cache.
 *
 * This command allows the application to re-scan installed Composer packages and
 * regenerate the internal manifest cache used for resolving service providers,
 * aliases, and other autoloaded components.
 *
 * ### Example:
 * ```bash
 * php omega package:discovery
 * ```
 *
 * This will output a list of discovered packages with status indicators.
 */
class PackageDiscoveryCommand extends Command
{
    /**
     * Command registration array.
     *
     * Defines the CLI pattern and the method to execute.
     *
     * @var array<int, array<string, mixed>>
     *
     * Example:
     * [
     *     [
     *         'pattern' => 'package:discovery',
     *         'fn'      => [self::class, 'discovery'],
     *     ],
     * ]
     */
    public static array $command = [
        [
            'pattern' => 'package:discovery',
            'fn'      => [self::class, 'discovery'],
        ],
    ];

    /**
     * Returns the help information for this command.
     *
     * This is used when displaying help in the CLI (e.g., via `php omega help`).
     *
     * @return array<string, array<string, string|string[]>>
     *
     * Example return:
     * [
     *     'commands' => [
     *         'package:discovery' => 'Discovery package in composer',
     *     ],
     *     'options' => [],
     *     'relation' => [],
     * ]
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'package:discovery' => 'Discovery package in composer',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    /**
     * Main execution logic for the `package:discovery` command.
     *
     * Triggers the `build()` method of the PackageManifest to regenerate the
     * package manifest cache file. Outputs the list of successfully processed packages.
     *
     * @param Application $app The application container with service bindings.
     *
     * @return int Exit status code: 0 for success, 1 for error.
     */
    public function discovery(Application $app): int
    {
        $package = $app[PackageManifest::class];
        info('Trying build package cache.')->out(false);
        try {
            $package->build();

            $packages = (fn () => $this->{'getPackageManifest'}())->call($package) ?? [];
            $style    = new Style();
            foreach (array_keys($packages) as $name) {
                $length = $this->getWidth(40, 60) - strlen($name) - 4;
                $style->push($name)->repeat('.', $length)->textDim()->push('DONE')->textGreen()->newLines();
            }
            $style->out(false);
        } catch (Throwable $th) {
            fail($th->getMessage())->out(false);
            fail('Can\'tests create package manifest cache file.')->out();

            return 1;
        }

        return 0;
    }
}
