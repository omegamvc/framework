<?php

/**
 * Part of Omega - Commands Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Database\Command;

use Exception;
use Omega\Database\Exception\QueryException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Omega\Database\Adapter\AbstractDatabaseAdapter;
use Omega\Support\Path;

use function glob;

/**
 * Migrate command class.
 *
 * The `MigrateCommand` is used to run database migrations. It looks for migration
 * files in the specified directory and executes them. You can also use the `--fresh`
 * option to delete all database tables before running migrations.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Command
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class MigrateCommand extends Command
{
    /**
     * Default command name.
     *
     * @var string Holds the default command name.
     */
    protected static string $defaultName = 'migrate';

    /**
     * Command constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('migrate');
    }

    /**
     * Configures the current command.
     *
     * This method configures the command description, options, and help information.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Migrates the database')
            ->addOption('fresh', null, InputOption::VALUE_NONE, 'Delete all tables before running the migrations')
            ->addOption('update', null, InputOption::VALUE_NONE, 'Apply only new migrations')
            ->setHelp('This command looks for all migration files and runs them');
    }

    /**
     * Executes the current command.
     *
     * This method runs database migrations by looking for migration files and executing
     * them in order. It also provides an option to delete all database tables before
     * running migrations.
     *
     * @param InputInterface  $input  Holds an instance of InputInterface.
     * @param OutputInterface $output Holds an instance of OutputInterface.
     * @return int Return 0 if everything went fine, or an exit code.
     * @throws Exception
     * @throws RuntimeException if database connection is invalid.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $paths = glob(Path::getPath('database', 'migrations/schemes/*.php'));

        if ($paths === false || count($paths) < 1) {
            $output->writeln('<warning>No migrations found.</warning>');

            return Command::SUCCESS;
        }

        $connection = app('database');
        assert($connection instanceof AbstractDatabaseAdapter);

        if ($input->getOption('fresh')) {
            $output->writeln('<comment>Dropping existing database tables.</comment>');
            $connection->dropTables();
        }

        if (! $connection->hasTable('migrations')) {
            $output->writeln('<comment>Creating migrations table.</comment>');
            $this->createMigrationsTable($connection);
        }

        if ($input->getOption('update')) {
            $output->writeln('<comment>Running only new migrations.</comment>');
            $this->runNewMigrations($paths, $connection, $output);
        } else {
            $this->runAllMigrations($paths, $connection, $output);
        }

        return Command::SUCCESS;
    }

    /**
     * Runs all the migrations in the given paths.
     *
     * @param array                   $paths      Holds the migration files paths.
     * @param AbstractDatabaseAdapter $connection Holds the database connection.
     * @param OutputInterface         $output     Holds the command output interface.
     * @return void
     * @throws Exception
     */
    private function runAllMigrations(array $paths, AbstractDatabaseAdapter $connection, OutputInterface $output): void
    {
        foreach ($paths as $path) {
            $this->runMigration($path, $connection, $output);
        }
    }

    /**
     * Runs only new migrations that are not in the migrations table.
     *
     * @param array                   $paths      Holds the migration files paths.
     * @param AbstractDatabaseAdapter $connection Holds the database connection.
     * @param OutputInterface         $output     Holds the command output interface.
     * @return void
     * @throws Exception
     * @throws QueryException
     */
    private function runNewMigrations(array $paths, AbstractDatabaseAdapter $connection, OutputInterface $output): void
    {
        $executedMigrations = array_column(
            $connection
                ->query()
                ->from('migrations')
                ->select('name')
                ->all(),
            'name'
        );

        foreach ($paths as $path) {
            [ $prefix, $file ]     = explode('_', $path);
            [ $class, $extension ] = explode('.', $file);

            if (!in_array($class, $executedMigrations)) {
                $this->runMigration($path, $connection, $output);
            } else {
                $output->writeln("<info>Migration {$class} already applied. Skipping.</info>");
            }
        }
    }

    /**
     * Runs a single migration file.
     *
     * @param string                  $path       Holds the migration file path.
     * @param AbstractDatabaseAdapter $connection Holds the database connection.
     * @param OutputInterface         $output     Holds the command output interface.
     * @return void
     * @throws Exception
     */
    private function runMigration(string $path, AbstractDatabaseAdapter $connection, OutputInterface $output): void
    {
        [ $prefix, $file ]     = explode('_', $path);
        [ $class, $extension ] = explode('.', $file);

        $className = "Database\\Migrations\\Schemes\\" . $class;

        require $path;

        $output->writeln("<info>Migrating: {$class}</info>");

        $obj = new $className();

        if (method_exists($obj, 'up')) {
            try {
                if ($connection->hasTable($obj->table)) {
                    $output->writeln(
                        "<info>Table {$obj->table} already exists. Skipping migration.</info>"
                    );

                    return;
                }

                $obj->up($connection);

                $connection
                ->query()
                ->from('migrations')
                ->insert([ 'name' ], [ 'name' => $class ]);
            } catch (Exception $e) {
                $output->writeln("<error>Migration {$class} failed. Rolling back...</error>");
                if (method_exists($obj, 'down')) {
                    $obj->down($connection);
                }

                throw $e;
            }
        } else {
            $output->writeln("<error>Class {$class} does not have an up method.</error>");
        }
    }

    /**
     * Create migration table.
     *
     * @param AbstractDatabaseAdapter $connection Holds an instance of AbstractDatabaseAdapter.
     *
     * @return void
     */
    private function createMigrationsTable(AbstractDatabaseAdapter $connection): void
    {
        if ($connection->hasTable('migrations')) {
            return;
        }

        $table = $connection->createTable('migrations');
        $table->id('id');
        $table->string('name');
        $table->execute();
    }
}
