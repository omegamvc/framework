<?php

declare(strict_types=1);

namespace Omega\Console;

use Omega\Console\Style\Style;
use Omega\Application\Application;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Container\Invoker\Exception\InvocationException;
use Omega\Container\Invoker\Exception\NotCallableException;
use Omega\Container\Invoker\Exception\NotEnoughParametersException;
use Omega\Support\Bootstrap\BootProviders;
use Omega\Support\Bootstrap\ConfigProviders;
use Omega\Support\Bootstrap\RegisterFacades;
use Omega\Support\Bootstrap\RegisterProviders;

class Console
{
    /**
     * Application Container.
     */
    protected Application $app;

    /** @var int console exit status */
    protected int $exitCode;

    /** @var array<int, class-string> Application bootstrap register. */
    protected array $bootstrappers = [
        ConfigProviders::class,
        RegisterFacades::class,
        RegisterProviders::class,
        BootProviders::class,
    ];

    /**
     * Set instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle input (arguments) kernel.
     *
     * @param string|array<int, string> $arguments
     * @return int Exit code
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     * @throws NotFoundException
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
        $similar = new Style('Did you mean?')->textLightYellow()->newLines();
        foreach ($this->getSimilarity($baseArgs, $commands, 0.8) as $term => $score) {
            $similar->push('    > ')->push($term)->textYellow()->newLines();
            $count++;
        }

        // if command not register
        if (0 === $count) {
            new Style()
                ->push('Command not found, run help command')->textRed()->newLines(2)
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
     * Register bootstrapper application.
     *
     * @return void
     * @throws InvalidDefinitionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function bootstrap(): void
    {
        $this->app->bootstrapWith($this->bootstrappers);
    }

    /**
     * Call command using know signature.
     * The signature not require php as prefix.
     * For better parse use `handle` method instead.
     *
     * @param string $signature
     * @param array<string, string|bool|int|null> $parameter
     * @return int
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     * @throws NotFoundException
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
     * Return similar from given array, compare with key.
     *
     * @param string[] $commands
     *
     * @return array<string, float> Sorted from similar
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
     * Calculate the similarity between two strings.
     *
     * @return float Similarity score (between 0 and 1)
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
     * Calculate the Jaro similarity between two strings.
     *
     * @return float the Jaro similarity score (between 0 and 1)
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
     * Get kernel exit status code.
     *
     * @return int Exit status code
     */
    public function exitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * Command route.
     *
     * @return CommandMap[]
     */
    protected function commands(): array
    {
        return Util::loadCommandFromConfig($this->app);
    }
}
