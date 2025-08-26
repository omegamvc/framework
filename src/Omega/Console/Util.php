<?php

declare(strict_types=1);

namespace Omega\Console;

use Omega\Application\Application;
use Omega\Config\ConfigRepository;

final class Util
{
    /**
     * Convert command from array to CommandMap.
     *
     * @return CommandMap[]
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
