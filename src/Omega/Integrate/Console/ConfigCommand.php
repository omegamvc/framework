<?php

declare(strict_types=1);

namespace Omega\Integrate\Console;

use Omega\Console\Command;
use Omega\Integrate\Application;
use Omega\Integrate\Bootstrap\ConfigProviders;
use Omega\Config\ConfigRepository;

use function Omega\Console\fail;
use function Omega\Console\ok;

class ConfigCommand extends Command
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
    public function printHelp()
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

    public function main(): int
    {
        $app = Application::getIntance();
        (new ConfigProviders())->bootstrap($app);

        $this->clear();
        $config        = $app->get(ConfigRepository::class)->getAll();
        $cached_config = '<?php return ' . var_export($config, true) . ';' . PHP_EOL;
        if (file_put_contents($app->getApplicationCachePath() . 'config.php', $cached_config)) {
            ok('Config file has successfully created.')->out();

            return 0;
        }
        fail('Cant build config cache.')->out();

        return 1;
    }

    public function clear(): int
    {
        if (file_exists($file = Application::getIntance()->getApplicationCachePath() . 'config.php')) {
            @unlink($file);
            ok('Clear config file has successfully.')->out();

            return 0;
        }

        return 1;
    }
}
