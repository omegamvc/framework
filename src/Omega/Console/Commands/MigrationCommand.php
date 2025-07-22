<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

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

use DI\DependencyException;
use DI\NotFoundException;
use DirectoryIterator;
use Exception;
use Omega\Collection\Collection;
use Omega\Console\Command;
use Omega\Console\Prompt;
use Omega\Console\Style\Style;
use Omega\Console\Traits\PrintHelpTrait;
use Omega\Database\Query\Query;
use Omega\Database\Schema\Table\Create;
use Omega\Support\Facades\DB;
use Omega\Support\Facades\PDO;
use Omega\Support\Facades\Schema;
use Throwable;

use function array_change_key_case;
use function count;
use function implode;
use function Omega\Console\fail;
use function Omega\Console\info;
use function Omega\Console\ok;
use function Omega\Console\style;
use function Omega\Console\warn;
use function pathinfo;
use function strlen;

use const PATHINFO_FILENAME;

/**
 * Handles all migration and database-related console commands.
 *
 * This class provides the main logic for running, rolling back, and refreshing migrations,
 * as well as for managing database creation, deletion, and status visualization.
 * It supports command-line options such as seeding, batching, dry-run mode, and forced execution
 * in production environments.
 *
 * @category   Omega
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
 * @property ?int        $take
 * @property ?int        $batch
 * @property bool        $force
 * @property string|bool $seed
 */
class MigrationCommand extends Command
{
    use PrintHelpTrait;

    /**
     * List of additional vendor migration paths.
     *
     * These paths will be scanned alongside the default migration path.
     *
     * @var string[]
     */
    public static array $vendorPaths = [];

    /**
     * List of available CLI commands for database and migration operations.
     *
     * Each command is defined by a pattern and its corresponding handler.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'migrate',
            'fn'      => [self::class, 'main'],
        ], [
            'pattern' => 'migrate:fresh',
            'fn'      => [self::class, 'fresh'],
        ], [
            'pattern' => 'migrate:reset',
            'fn'      => [self::class, 'reset'],
        ], [
            'pattern' => 'migrate:refresh',
            'fn'      => [self::class, 'refresh'],
        ], [
            'pattern' => 'migrate:rollback',
            'fn'      => [self::class, 'rollback'],
        ], [
            'pattern' => ['database:create', 'db:create'],
            'fn'      => [self::class, 'databaseCreate'],
        ], [
            'pattern' => ['database:drop', 'db:drop'],
            'fn'      => [self::class, 'databaseDrop'],
        ], [
            'pattern' => ['database:show', 'db:show'],
            'fn'      => [self::class, 'databaseShow'],
        ], [
            'pattern' => 'migrate:status',
            'fn'      => [self::class, 'status'],
        ], [
            'pattern' => 'migrate:init',
            'fn'      => [self::class, 'initializeMigration'],
        ],
    ];

    /**
     * Get help information for all supported commands and their options.
     *
     * Returns a structured array describing available commands, their descriptions,
     * supported options, and the relationships between them.
     *
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'migrate'                  => 'Run migration (up)',
                'migrate:fresh'            => 'Drop database and run migrations',
                'migrate:reset'            => 'Rolling back all migrations (down)',
                'migrate:refresh'          => 'Rolling back and run migration all',
                'migrate:rollback'         => 'Rolling back last migrations (down)',
                'migrate:init'             => 'Initialize migration table',
                'migrate:status'           => 'Show migration status.',
                'database:create'          => 'Create database',
                'database:drop'            => 'Drop database',
                'database:show'            => 'Show database table',
            ],
            'options'   => [
                '--take'              => 'Limit of migrations to be run.',
                '--batch'             => 'Batch migration execution.',
                '--dry-run'           => 'Execute migration but only get query output.',
                '--force'             => 'Force running migration/database query in production.',
                '--seed'              => 'Run seeder after migration.',
                '--seed-namespace'    => 'Run seeder after migration using class namespace.',
                '--yes'               => 'Accept it without having it ask any questions',
            ],
            'relation'  => [
                'migrate'                   => ['--seed', '--dry-run', '--force'],
                'migrate:fresh'             => ['--seed', '--dry-run', '--force'],
                'migrate:reset'             => ['--dry-run', '--force'],
                'migrate:refresh'           => ['--seed', '--dry-run', '--force'],
                'migrate:rollback'          => ['--batch', '--take', '--dry-run', '--force'],
                'database:create'           => ['--force'],
                'database:drop'             => ['--force'], ],
        ];
    }

    /**
     * Retrieve the configured database name from the application container.
     *
     * @return string
     * @throws DependencyException If the service cannot be resolved.
     * @throws NotFoundException If the 'dsn.sql' key is not found.
     */
    private function DbName(): string
    {
        return app()->get('dsn.sql')['database_name'];
    }

    /**
     * Check if the operation is allowed in development or production mode.
     *
     * If not in development mode, prompts the user to confirm before continuing.
     *
     * @return bool True if allowed, false otherwise.
     * @throws Exception If an error occurs during user prompt.
     */
    private function runInDev(): bool
    {
        if (app()->isDev() || $this->force) {
            return true;
        }

        /* @var bool */
        return (new Prompt(style('Running migration/database in production?')->textRed(), [
            'yes' => fn () => true,
            'no'  => fn () => false,
        ], 'no'))
            ->selection([
                style('yes')->textDim(),
                ' no',
            ])
            ->option();
    }

    /**
     * Prompt the user to confirm an action.
     *
     * Returns true if the user accepts or if the `--yes` option is set.
     *
     * @param Style|string $message The confirmation message.
     * @return bool
     * @throws Exception If an error occurs during user prompt.
     */
    private function confirmation(Style|string $message): bool
    {
        if ($this->option('yes', false)) {
            return true;
        }

        /* @var bool */
        return (new Prompt($message, [
            'yes' => fn () => true,
            'no'  => fn () => false,
        ], 'no'))
        ->selection([
            style('yes')->textDim(),
            ' no',
        ])
        ->option();
    }

    /**
     * Build the list of migration files to be executed.
     *
     * Determines which migrations are pending or due to be run based on the current
     * batch status. Also updates the internal migration tracking table.
     *
     * @param false|int $batch Batch number passed by reference. If false, the next available batch is used.
     * @return Collection<string, array<string, string>> A collection of migrations with file path and batch number.
     */
    public function baseMigrate(false|int &$batch = false): Collection
    {
        $migrationBatch = $this->getMigrationTable();
        $heights        = $migrationBatch->length() > 0
            ? $migrationBatch->max() + 1
            : 0;
        $batch = false === $batch ? $heights : $batch;

        $paths   = [migration_path(), ...static::$vendorPaths];
        $migrate = new Collection([]);
        foreach ($paths as $dir) {
            foreach (new DirectoryIterator($dir) as $file) {
                if ($file->isDot() | $file->isDir()) {
                    continue;
                }

                $migrationName = pathinfo($file->getBasename(), PATHINFO_FILENAME);
                $hasMigration   = $migrationBatch->has($migrationName);

                if (!$batch && $hasMigration) {
                    if ($migrationBatch->get($migrationName) <= $heights - 1) {
                        $migrate->set($migrationName, [
                            'file_name' => $dir . $file->getFilename(),
                            'batch'     => $migrationBatch->get($migrationName),
                        ]);
                        continue;
                    }
                }

                if (false === $hasMigration) {
                    $migrate->set($migrationName, [
                        'file_name' => $dir . $file->getFilename(),
                        'batch'     => $heights,
                    ]);
                    $this->insertMigrationTable([
                        'migration' => $migrationName,
                        'batch'     => $heights,
                    ]);
                    continue;
                }

                if ($migrationBatch->get($migrationName) <= $batch) {
                    $migrate->set($migrationName, [
                        'file_name' => $dir . $file->getFilename(),
                        'batch'     => $migrationBatch->get($migrationName),
                    ]);
                    /** @noinspection PhpUnnecessaryStopStatementInspection */
                    continue;
                }
            }
        }

        return $migrate;
    }

    /**
     * Run all pending migrations.
     *
     * This is the main command handler for `migrate`.
     *
     * @return int Exit status code.
     * @throws Exception If a migration fails or execution is interrupted.
     */
    public function main(): int
    {
        return $this->migration();
    }

    /**
     * Perform the migration process.
     *
     * Executes all migration files scheduled for the current batch. Handles both
     * dry-run and execution modes, and displays output accordingly.
     *
     * @param bool $silent If true, suppresses user confirmation prompts.
     * @return int Exit status code.
     * @throws Exception If a migration fails or execution is interrupted.
     */
    public function migration(bool $silent = false): int
    {
        if (false === $this->runInDev() && false === $silent) {
            return 2;
        }

        $print   = new Style();
        $width   = $this->getWidth(40, 60);
        $batch   = false;
        $migrate = $this->baseMigrate($batch);
        $migrate
            ->filter(static fn ($value): bool => $value['batch'] == $batch)
            ->sort();

        /** @noinspection DuplicatedCode */
        $print->tap(info('Running migration'));

        foreach ($migrate as $key => $val) {
            $schema = require_once $val['file_name'];
            $up     = new Collection($schema['up'] ?? []);

            if ($this->option('dry-run')) {
                $up->each(function ($item) use ($print) {
                    $print->push($item->__toString())->textDim()->newLines(2);

                    return true;
                });
                continue;
            }

            $print->push($key)->textDim();
            $print->repeat('.', $width - strlen($key))->textDim();

            try {
                $success = $up->every(fn ($item) => $item->execute());
            } catch (Throwable $th) {
                $success = false;
                fail($th->getMessage())->out(false);
            }

            if ($success) {
                $print->push('DONE')->textGreen()->newLines();
                continue;
            }

            $print->push('FAIL')->textRed()->newLines();
        }

        $print->out();

        return $this->seed();
    }

    /**
     * Drops and recreates the database, then runs all migrations and seeds.
     *
     * @param bool $silent If true, suppresses confirmation prompts.
     * @return int Exit status code.
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Exception
     */
    public function fresh(bool $silent = false): int
    {
        // drop and recreate database
        if (($drop = $this->databaseDrop($silent)) > 0) {
            return $drop;
        }
        if (($create = $this->databaseCreate(true)) > 0) {
            return $create;
        }

        // run migration

        $print   = new Style();
        $migrate = $this->baseMigrate()->sort();
        $width   = $this->getWidth(40, 60);

        /** @noinspection DuplicatedCode */
        $print->tap(info('Running migration'));

        foreach ($migrate as $key => $val) {
            /** @noinspection DuplicatedCode */
            $schema = require_once $val['file_name'];
            $up     = new Collection($schema['up'] ?? []);

            if ($this->option('dry-run')) {
                $up->each(function ($item) use ($print) {
                    $print->push($item->__toString())->textDim()->newLines(2);

                    return true;
                });
                continue;
            }

            $print->push($key)->textDim();
            $print->repeat('.', $width - strlen($key))->textDim();

            try {
                $success = $up->every(fn ($item) => $item->execute());
            } catch (Throwable $th) {
                $success = false;
                fail($th->getMessage())->out(false);
            }

            if ($success) {
                $print->push('DONE')->textGreen()->newLines();
                continue;
            }

            $print->push('FAIL')->textRed()->newLines();
        }

        $print->out();

        return $this->seed();
    }

    /**
     * Rolls back all existing migrations.
     *
     * @param bool $silent If true, suppresses confirmation prompts.
     * @return int Exit status code.
     * @throws Exception
     */
    public function reset(bool $silent = false): int
    {
        if (false === $this->runInDev() && false === $silent) {
            return 2;
        }
        info('Rolling back all migrations')->out(false);
        return $this->rollbacks(false, 0);
    }

    /**
     * Resets the database and reruns all migrations.
     *
     * @return int Exit status code.
     * @throws Exception
     */
    public function refresh(): int
    {
        if (false === $this->runInDev()) {
            return 2;
        }

        if (($reset = $this->reset(true)) > 0) {
            return $reset;
        }
        if (($migration = $this->migration(true)) > 0) {
            return $migration;
        }

        return 0;
    }

    /**
     * Rolls back a specific batch of migrations.
     *
     * Requires the 'batch' option to be specified.
     *
     * @return int Exit status code.
     */
    public function rollback(): int
    {
        if (false === ($batch = $this->option('batch', false))) {
            fail('batch is required.')->out();

            return 1;
        }
        $take    = $this->take;
        $message = "Rolling {$take} back migrations.";
        if ($take < 0) {
            $take    = 0;
            $message = 'Rolling back migrations.';
        }
        info($message)->out(false);

        return $this->rollbacks((int) $batch, (int) $take);
    }

    /**
     * Executes the rollback process for a given batch and number of steps.
     *
     * @param int|false $batch The target batch number or false to rollback all.
     * @param int $take Number of migrations to rollback. 0 rolls back all from the batch.
     * @return int Exit status code.
     */
    public function rollbacks(int|false $batch, int $take): int
    {
        $print   = new Style();
        $width   = $this->getWidth(40, 60);

        $migrate = false === $batch
            ? $this->baseMigrate($batch)
            : $this->baseMigrate($batch)->filter(static fn ($value): bool => $value['batch'] >= $batch - $take);

        foreach ($migrate->sortDesc() as $key => $val) {
            $schema = require_once $val['file_name'];
            $down   = new Collection($schema['down'] ?? []);

            /** @noinspection DuplicatedCode */
            if ($this->option('dry-run')) {
                $down->each(function ($item) use ($print) {
                    $print->push($item->__toString())->textDim()->newLines(2);

                    return true;
                });
                continue;
            }

            $print->push($key)->textDim();
            $print->repeat('.', $width - strlen($key))->textDim();

            try {
                $success = $down->every(fn ($item) => $item->execute());
            } catch (Throwable $th) {
                $success = false;
                fail($th->getMessage())->out(false);
            }

            if ($success) {
                $print->push('DONE')->textGreen()->newLines();
                continue;
            }

            $print->push('FAIL')->textRed()->newLines();
        }

        $print->out();

        return 0;
    }

    /**
     * Creates the application database if it doesn't already exist.
     *
     * @param bool $silent If true, suppresses confirmation prompts.
     * @return int Exit status code.
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Exception
     */
    public function databaseCreate(bool $silent = false): int
    {
        $dbName  = $this->DbName();
        $message = style("Do you want to create database `{$dbName}`?")->textBlue();

        if (false === $silent && (!$this->runInDev() || !$this->confirmation($message))) {
            return 2;
        }

        info("creating database `{$dbName}`")->out(false);

        $success = Schema::create()->database($dbName)->ifNotExists()->execute();

        if ($success) {
            ok("success create database `{$dbName}`")->out(false);

            $this->initializeMigration();

            return 0;
        }

        fail("cant created database `{$dbName}`")->out(false);

        return 1;
    }

    /**
     * Drops the application database if it exists.
     *
     * @param bool $silent If true, suppresses confirmation prompts.
     * @return int Exit status code.
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Exception
     */
    public function databaseDrop(bool $silent = false): int
    {
        $dbName  = $this->DbName();
        $message = style("Do you want to drop database `{$dbName}`?")->textRed();

        if (false === $silent && (!$this->runInDev() || !$this->confirmation($message))) {
            return 2;
        }

        info("try to drop database `{$dbName}`")->out(false);

        /** @noinspection PhpRedundantOptionalArgumentInspection */
        $success = Schema::drop()->database($dbName)->ifExists(true)->execute();

        if ($success) {
            ok("success drop database `{$dbName}`")->out(false);

            return 0;
        }

        fail("cant drop database `{$dbName}`")->out(false);

        return 1;
    }

    /**
     * Display information about the current database or a specific table.
     *
     * If the --table-name option is provided, delegates to tableShow().
     * Otherwise, lists all tables in the database with size and creation time.
     *
     * @return int 0 on success, 2 if no tables are found
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function databaseShow(): int
    {
        if ($this->option('table-name')) {
            /** @noinspection PhpRedundantOptionalArgumentInspection */
            return $this->tableShow($this->option('table-name', null));
        }

        $dbName = $this->DbName();
        $width   = $this->getWidth(40, 60);
        info('showing database')->out(false);

        $tables = PDO::getInstance()
            ->query('SHOW DATABASES')
            ->query('
                SELECT table_name, create_time, ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024) AS `size`
                FROM information_schema.tables
                WHERE table_schema = :db_name')
            ->bind(':db_name', $dbName)
            ->resultSet();

        if (0 === count($tables)) {
            warn('table is empty try to run migration')->out();

            return 2;
        }

        foreach ($tables as $table) {
            $table  = array_change_key_case($table);
            $name   = $table['table_name'];
            $time   = $table['create_time'];
            $size   = $table['size'];
            $length = strlen($name) + strlen($time) + strlen($size);

            style($name)
                ->push(' ' . $size . ' Mb ')->textDim()
                ->repeat('.', $width - $length)->textDim()
                ->push(' ' . $time)
                ->out();
        }

        return 0;
    }

    /**
     * Display schema details for a specific table.
     *
     * Shows column names, types, and attributes such as primary key or nullable.
     *
     * @param string $table The table name
     * @return int Always returns 0
     */
    public function tableShow(string $table): int
    {
        $table = (new Query(PDO::getInstance()))->table($table)->info();
        $print = new Style("\n");
        $width = $this->getWidth(40, 60);

        $print->push('column')->textYellow()->bold()->resetDecorate()->newLines();
        foreach ($table as $column) {
            $willPrint = [];

            if ($column['IS_NULLABLE'] === 'YES') {
                $willPrint[] = 'nullable';
            }
            if ($column['COLUMN_KEY'] === 'PRI') {
                $willPrint[] = 'primary';
            }

            $info   = implode(', ', $willPrint);
            $length = strlen($column['COLUMN_NAME']) + strlen($column['COLUMN_TYPE']) + strlen($info);

            $print->push($column['COLUMN_NAME'])->bold()->resetDecorate();
            $print->push(' ' . $info . ' ')->textDim();
            $print->repeat('.', $width - $length)->textDim();
            $print->push(' ' . $column['COLUMN_TYPE']);
            $print->newLines();
        }

        $print->out();

        return 0;
    }

    /**
     * Display the migration status for the current database.
     *
     * Lists all migration file names with their corresponding batch numbers.
     *
     * @return int Always returns 0
     */
    public function status(): int
    {
        $print = new Style();
        $print->tap(info('show migration status'));
        $width = $this->getWidth(40, 60);
        foreach ($this->getMigrationTable() as $migrationName => $batch) {
            $length = strlen($migrationName) + strlen((string) $batch);
            $print
                ->push($migrationName)
                ->push(' ')
                ->repeat('.', $width - $length)->textDim()
                ->push(' ')
                ->push($batch)
                ->newLines();
        }

        $print->out();

        return 0;
    }

    /**
     * Run the seed command after a migration, if enabled.
     *
     * Supports optional class name or namespace via CLI options.
     * Skips seeding in dry-run mode.
     *
     * @return int Exit code of the seed command, or 0 if no seed is run
     * @throws Exception
     */
    private function seed(): int
    {
        if ($this->option('dry-run', false)) {
            return 0;
        }
        if ($this->seed) {
            $seed = true === $this->seed ? null : $this->seed;

            return (new SeedCommand([], ['class' => $seed]))->main();
        }

        $namespace = $this->option('seed-namespace', false);
        if ($namespace) {
            $namespace = true === $namespace ? null : $namespace;

            return (new SeedCommand([], ['name-space' => $namespace]))->main();
        }

        return 0;
    }

    /**
     * Check if the migration table exists in the current database.
     *
     * @return bool True if the table exists, false otherwise
     * @throws DependencyException
     * @throws NotFoundException
     */
    private function hasMigrationTable(): bool
    {
        $result = PDO::getInstance()->query(
            "SELECT COUNT(table_name) as total
            FROM information_schema.tables
            WHERE table_schema = :dbname
            AND table_name = 'migration'"
        )->bind(':dbname', $this->DbName())
        ->single();

        if ($result) {
            return $result['total'] > 0;
        }

        return false;
    }

    /**
     * Create the migration table schema.
     *
     * Defines columns: `migration` (unique), `batch`.
     *
     * @return bool True on success, false on failure
     */
    private function createMigrationTable(): bool
    {
        return Schema::table('migration', function (Create $column) {
            $column('migration')->varchar(100)->notNull();
            $column('batch')->int(4)->notNull();

            $column->unique('migration');
        })->execute();
    }

    /**
     * Retrieve all entries in the migration table as a collection.
     *
     * The result is a key-value pair where the key is the migration name,
     * and the value is the batch number.
     *
     * @return Collection<string, int> Migration file names and batch numbers
     */
    private function getMigrationTable(): Collection
    {
        /** @var Collection<string, int> $pair */
        $pair = DB::table('migration')
            ->select()
            ->get()
            ->assocBy(static fn ($item) => [$item['migration'] => (int) $item['batch']]);

        return $pair;
    }

    /**
     * Insert a migration record into the migration table.
     *
     * @param array<string, string|int> $migration Key-value pair for insertion
     * @return bool True on success, false on failure
     */
    private function insertMigrationTable(array $migration): bool
    {
        return DB::table('migration')
            ->insert()
            ->values($migration)
            ->execute()
        ;
    }

    /**
     * Initialize the migration system for the current database.
     *
     * Creates the migration table if it does not already exist.
     *
     * @return int 0 if successful or already exists, 1 on failure
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function initializeMigration(): int
    {
        $hasMigrationTable = $this->hasMigrationTable();

        if ($hasMigrationTable) {
            info('Migration table already exist on your database table.')->out(false);

            return 0;
        }

        if ($this->createMigrationTable()) {
            ok('Success create migration table.')->out(false);

            return 0;
        }

        fail('Migration table cant be create.')->out(false);

        return 1;
    }

    /**
     * Add a path to vendor migration directories.
     *
     * Used to load migration files from external packages.
     *
     * @param string $path Path to the vendor migration folder
     * @return void
     */
    public static function addVendorMigrationPath(string $path): void
    {
        static::$vendorPaths[] = $path;
    }

    /**
     * Clear all registered vendor migration paths.
     *
     * @return void
     */
    public static function flushVendorMigrationPaths(): void
    {
        static::$vendorPaths = [];
    }
}
