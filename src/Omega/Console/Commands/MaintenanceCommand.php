<?php

declare(strict_types=1);

namespace Omega\Console\Commands;

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Console\AbstractCommand;
use Omega\Console\Traits\PrintHelpTrait;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function Omega\Console\info;
use function Omega\Console\ok;
use function Omega\Console\warn;

class MaintenanceCommand extends AbstractCommand
{
    use PrintHelpTrait;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'up',
            'fn'      => [self::class, 'up'],
        ], [
            'pattern' => 'down',
            'fn'      => [self::class, 'down'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
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
     * @return int
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function down(): int
    {
        if (app()->isDownMaintenanceMode()) {
            warn('Application is already under maintenance mode.')->out();

            return 1;
        }

        if (false === file_exists($down = get_path('path.storage') . 'app' . DIRECTORY_SEPARATOR . 'down')) {
            file_put_contents($down, file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'down'));
        }

        file_put_contents(get_path('path.storage') . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'maintenance'));
        ok('Successfully, your application now in under maintenance.')->out();

        return 0;
    }

    /**
     * @return int
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function up(): int
    {
        if (false === app()->isDownMaintenanceMode()) {
            warn('Application is not in maintenance mode.')->out();

            return 1;
        }

        if (false === unlink($up = get_path('path.storage') . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php')) {
            warn('Application stil maintenance mode.')->out(false);
            info("Remove manually maintenance file in `$up`.")->out();

            return 1;
        }

        ok('Successfully, your apllication now live.')->out();

        return 0;
    }
}
