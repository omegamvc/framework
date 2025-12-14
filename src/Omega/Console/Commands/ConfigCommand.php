<?php

declare(strict_types=1);

namespace Omega\Console\Commands;

use Omega\Application\Application;
use Omega\Config\ConfigRepository;
use Omega\Console\AbstractCommand;
use Omega\Container\Exceptions\BindingResolutionException;
use Omega\Container\Exceptions\CircularAliasException;
use Omega\Container\Exceptions\EntryNotFoundException;
use Omega\Support\Bootstrap\ConfigProviders;

use ReflectionException;
use function file_exists;
use function file_put_contents;
use function Omega\Console\error;
use function Omega\Console\info;
use function unlink;

use const PHP_EOL;

class ConfigCommand extends AbstractCommand
{
    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'config:cache',
            'fn'      => [self::class, 'main'],
        ], [
            'pattern' => 'config:clear',
            'fn'      => [self::class, 'clear'],
        ],
    ];

    /**
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
     * @return int
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function main(): int
    {
        $app = Application::getInstance();
        new ConfigProviders()->bootstrap($app);

        $this->clear();
        $config       = $app->get(ConfigRepository::class)->getAll();
        $cachedConfig = '<?php return ' . var_export($config, true) . ';' . PHP_EOL;
        if (file_put_contents($app->getApplicationCachePath() . 'config.php', $cachedConfig)) {
            info('Configuration cached successfully.')->out();

            return 0;
        }
        error('Cant build config cache.')->out();

        return 1;
    }

    /**
     * @return int
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function clear(): int
    {
        if (file_exists($file = Application::getInstance()->getApplicationCachePath() . 'config.php')) {
            @unlink($file);
            info('Configuration cache cleared successfully.')->out();

            return 0;
        }

        return 1;
    }
}
