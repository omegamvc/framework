<?php

declare(strict_types=1);

namespace Omega\Console\Commands;

use Omega\Application\Application;
use Omega\Config\ConfigRepository;
use Omega\Console\AbstractCommand;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Support\Bootstrap\ConfigProviders;

use function file_exists;
use function file_put_contents;
use function Omega\Console\fail;
use function Omega\Console\ok;
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
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidDefinitionException
     */
    public function main(): int
    {
        $app = Application::getInstance();
        new ConfigProviders()->bootstrap($app);

        $this->clear();
        $config       = $app->get(ConfigRepository::class)->getAll();
        $cachedConfig = '<?php return ' . var_export($config, true) . ';' . PHP_EOL;
        if (file_put_contents($app->getApplicationCachePath() . 'config.php', $cachedConfig)) {
            ok('Config file has successfully created.')->out();

            return 0;
        }
        fail('Cant build config cache.')->out();

        return 1;
    }

    /**
     * @return int
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
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
