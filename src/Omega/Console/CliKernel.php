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
use Omega\Console\Style\Style;
use Omega\Support\Bootstrap\RegisterFacades;
use Omega\Support\Bootstrap\RegisterProviders;
use Omega\Support\Bootstrap\BootProviders;
use Omega\Support\Bootstrap\ConfigProviders;

use function array_fill;
use function array_merge;
use function arsort;
use function explode;
use function floor;
use function is_int;
use function max;
use function min;
use function strlen;
use function strtolower;

/**
 * Class CliKernel
 *
 * Handles the CLI application lifecycle, including command resolution,
 * argument parsing, provider bootstrapping, and similarity suggestions
 * for unrecognized commands.
 *
 * @category  Omega
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class CliKernel
{
    /** @var int The exit code returned by the command execution. */
    protected int $exitCode;

    /** @var array<int, class-string> The list of application service providers to bootstrap. */
    protected array $providers = [
        ConfigProviders::class,
        RegisterFacades::class,
        RegisterProviders::class,
        BootProviders::class,
    ];

    /**
     * Create a new CliKernel instance.
     *
     * @param Application $app The current application instance.
     * @return void
     */
    public function __construct(protected Application $app)
    {
    }

    /**
     * Handle the given command-line arguments.
     *
     * @param string|array<int, string> $arguments CLI arguments.
     * @return int Exit code.
     */
    public function handle(array|string $arguments): int
    {
        // handle command empty
        $baseArgs = $arguments[1] ?? '--help';
        $commands = [];

        $this->bootstrap();

        foreach ($this->commands() as $cmd) {
            $commands = array_merge($commands, $cmd->patterns(), $cmd->cmd());

            if ($cmd->isMatch($baseArgs)) {
                $class = $cmd->class();
                $this->app->set($class, fn () => new $class($arguments, $cmd->defaultOption()));

                $call = $this->app->call($cmd->call());

                return $this->exitCode = (is_int($call) ? $call : 0);
            }
        }

        // did you mean
        $count   = 0;
        $similar = (new Style('Did you mean?'))->textLightYellow()->newLines();
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        foreach ($this->getSimilarity($baseArgs, $commands, 0.8) as $term => $score) {
            $similar->push('    > ')->push($term)->textYellow()->newLines();
            $count++;
        }

        // if command not register
        if (0 === $count) {
            (new Style())
                ->push('Command Not Found, run help command')->textRed()->newLines(2)
                ->push('> ')->textDim()
                ->push('php ')->textYellow()
                ->push('omega ')
                ->push('--help')->textDim()
                ->newLines()
                ->out()
            ;

            return $this->exitCode = 1;
        }

        $similar->out();

        return $this->exitCode = 1;
    }

    /**
     * Bootstrap the application by loading the registered providers.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        $this->app->bootstrapWith($this->providers);
    }

    /**
     * Call a command programmatically using its signature.
     *
     * Note: For parsing a full CLI string, prefer the `handle()` method.
     *
     * @param string                              $signature Command signature (e.g. "make:controller HomeController").
     * @param array<string, string|bool|int|null> $parameter Optional parameters to pass.
     * @return int Exit code.
     */
    public function call(string $signature, array $parameter = []): int
    {
        $arguments = explode(' ', $signature);
        $baseArgs  = $arguments[1] ?? '--help';

        $this->bootstrap();

        foreach ($this->commands() as $cmd) {
            if ($cmd->isMatch($baseArgs)) {
                $class = $cmd->class();
                $this->app->set($class, fn () => new $class($arguments, $parameter));

                $call = $this->app->call($cmd->call());

                return is_int($call) ? $call : 0;
            }
        }

        return 1;
    }

    /**
     * Suggest similar command names using string similarity.
     *
     * @param string   $find      The command entered by the user.
     * @param string[] $commands  A list of all known command patterns.
     * @param float    $threshold Minimum similarity score (0.0–1.0).
     * @return array<string, float> Sorted list of similar commands with their scores.
     * @noinspection PhpSameParameterValueInspection
     */
    private function getSimilarity(string $find, array $commands, float $threshold = 0.8): array
    {
        $closest   = [];
        $findLower = strtolower($find);

        foreach ($commands as $command) {
            $commandLower = strtolower($command);
            $similarity   = $this->jaroWinkler($findLower, $commandLower);

            if ($similarity >= $threshold) {
                $closest[$command] = $similarity;
            }
        }

        arsort($closest);

        return $closest;
    }

    /**
     * Compute the Jaro-Winkler similarity between two strings.
     *
     * @param string $find
     * @param string $command
     * @return float Similarity score (0.0–1.0).
     */
    private function jaroWinkler(string $find, string $command): float
    {
        $jaro = $this->jaro($find, $command);

        // Calculate the prefix length (maximum of 4 characters)
        $prefixLength    = 0;
        $maxPrefixLength = min(strlen($find), strlen($command), 4);
        for ($i = 0; $i < $maxPrefixLength; $i++) {
            if ($find[$i] !== $command[$i]) {
                break;
            }
            $prefixLength++;
        }

        return $jaro + ($prefixLength * 0.1 * (1 - $jaro));
    }

    /**
     * Compute the Jaro similarity between two strings.
     *
     * @param string $find
     * @param string $command
     * @return float Similarity score (0.0–1.0).
     */
    private function jaro(string $find, string $command): float
    {
        $len1 = strlen($find);
        $len2 = strlen($command);

        if ($len1 === 0) {
            return $len2 === 0 ? 1.0 : 0.0;
        }

        $matchDistance = (int) floor(max($len1, $len2) / 2) - 1;

        $str1Matches = array_fill(0, $len1, false);
        $str2Matches = array_fill(0, $len2, false);

        $matches        = 0;
        $transpositions = 0;

        // Find matching characters
        for ($i = 0; $i < $len1; $i++) {
            $start = max(0, $i - $matchDistance);
            $end   = min($i + $matchDistance + 1, $len2);

            for ($j = $start; $j < $end; $j++) {
                if ($str2Matches[$j] || $find[$i] !== $command[$j]) {
                    continue;
                }
                $str1Matches[$i] = true;
                $str2Matches[$j] = true;
                $matches++;
                break;
            }
        }

        if ($matches === 0) {
            return 0.0;
        }

        // Count transpositions
        $k = 0;
        for ($i = 0; $i < $len1; $i++) {
            if (false === $str1Matches[$i]) {
                continue;
            }
            while (false === $str2Matches[$k]) {
                $k++;
            }
            if ($find[$i] !== $command[$k]) {
                $transpositions++;
            }
            $k++;
        }

        $transpositions /= 2;

        return (($matches / $len1) + ($matches / $len2) + (($matches - $transpositions) / $matches)) / 3.0;
    }

    /**
     * Get the exit status code from the last executed command.
     *
     * @return int Exit code.
     */
    public function exitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * Load the list of available CLI commands from configuration.
     *
     * @return CommandMap[] Array of registered command maps.
     */
    protected function commands(): array
    {
        return Util::loadCommandFromConfig($this->app);
    }
}
