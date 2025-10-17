<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

declare(strict_types=1);

namespace Omega\Console\Commands;

use DirectoryIterator;
use Exception;
use Omega\Collection\Collection;
use Omega\Console\AbstractCommand;
use Omega\Console\Prompt;
use Omega\Console\Style\Style;
use Omega\Console\Traits\PrintHelpTrait;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Database\MySchema\MyPDO;
use Omega\Database\MySchema\Table\Create;
use Omega\Support\Facades\DB;
use Omega\Support\Facades\PDO;
use Omega\Support\Facades\Schema;
use Throwable;

use function Omega\Console\error;
use function Omega\Console\info;
use function Omega\Console\style;
use function Omega\Console\success;
use function Omega\Console\warn;

use const PATHINFO_FILENAME;

/**
 * @property ?int        $take
 * @property ?int        $batch
 * @property bool        $force
 * @property string|bool $seed
 */
class MigrationCommand extends AbstractCommand
{
    use PrintHelpTrait;

    /**
     * Register vendor migration path.
     *
     * @var string[]
     */
    public static array $vendorPaths = [];

    /**
     * Register command.
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
                '--database'          => 'Target database to use.',
            ],
            'relation'  => [
                'migrate'                   => ['--seed', '--dry-run', '--force'],
                'migrate:fresh'             => ['--seed', '--dry-run', '--force'],
                'migrate:reset'             => ['--dry-run', '--force'],
                'migrate:refresh'           => ['--seed', '--dry-run', '--force'],
                'migrate:rollback'          => ['--batch', '--take', '--dry-run', '--force'],
                'database:create'           => ['--database', '--force'],
                'database:drop'             => ['--database', '--force'],
                'database:show'             => ['--database', '--force'],
            ],
        ];
    }

    /**
     * @return string
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    private function DbName(): string
    {
        return $this->option('database', app()->get(MyPDO::class)->getDatabase());
    }

    /**
     * @throws InvalidDefinitionException
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Exception
     */
    private function runInDev(): bool
    {
        if (app()->isDev() || $this->force) {
            return true;
        }

        /* @var bool */
        return new Prompt(style('Running migration/database in production?')->textRed(), [
            'yes' => fn () => true,
            'no'  => fn () => false,
        ], 'no')
            ->selection([
                style('yes')->textDim(),
                ' no',
            ])
            ->option();
    }

    /**
     * @param string|Style $message
     * @return bool
     * @throws Exception
     */
    private function confirmation(Style|string $message): bool
    {
        if ($this->option('yes', false)) {
            return true;
        }

        /* @var bool */
        return new Prompt($message, [
            'yes' => fn () => true,
            'no'  => fn () => false,
        ], 'no')
        ->selection([
            style('yes')->textDim(),
            ' no',
        ])
        ->option();
    }

    /**
     * Get migration list.
     *
     * @param false|int $batch
     * @return Collection<string, array<string, string>>
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    public function baseMigrate(false|int &$batch = false): Collection
    {
        $migrationBatch = $this->getMigrationTable();
        $higher         = $migrationBatch->length() > 0
            ? $migrationBatch->max() + 1
            : 0;
        $batch = false === $batch ? $higher : $batch;

        $paths   = [get_path('path.migration'), ...static::$vendorPaths];
        $migrate = new Collection([]);
        foreach ($paths as $dir) {
            foreach (new DirectoryIterator($dir) as $file) {
                if ($file->isDot() | $file->isDir()) {
                    continue;
                }

                $migrationName = pathinfo($file->getBasename(), PATHINFO_FILENAME);
                $hasMigration  = $migrationBatch->has($migrationName);

                if (!$batch && $hasMigration) {
                    if ($migrationBatch->get($migrationName) <= $higher - 1) {
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
                        'batch'     => $higher,
                    ]);
                    $this->insertMigrationTable([
                        'migration' => $migrationName,
                        'batch'     => $higher,
                    ]);
                    continue;
                }

                if ($migrationBatch->get($migrationName) <= $batch) {
                    $migrate->set($migrationName, [
                        'file_name' => $dir . $file->getFilename(),
                        'batch'     => $migrationBatch->get($migrationName),
                    ]);
                }
            }
        }

        return $migrate;
    }

    /**
     * @return int
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    public function main(): int
    {
        return $this->migration();
    }

    /**
     * @param bool $silent
     * @return int
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
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
                error($th->getMessage())->out(false);
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
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     * @throws DependencyException
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
                error($th->getMessage())->out(false);
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
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     * @throws DependencyException
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
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     * @throws DependencyException
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
     * @throws InvalidDefinitionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function rollback(): int
    {
        if (false === ($batch = $this->option('batch', false))) {
            error('batch is required.')->out();

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
     * Rolling backs migration.
     *
     * @param false|int $batch
     * @param int $take
     * @return int
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    public function rollbacks(false|int $batch, int $take): int
    {
        $print   = new Style();
        $width   = $this->getWidth(40, 60);

        $migrate = false === $batch
            ? $this->baseMigrate($batch)
            : $this->baseMigrate($batch)->filter(static fn ($value): bool => $value['batch'] >= $batch - $take);

        foreach ($migrate->sortDesc() as $key => $val) {
            $schema = require_once $val['file_name'];
            $down   = new Collection($schema['down'] ?? []);

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

                if ($success) {
                    $success = $this->deleteMigrationTable((int) $val['batch']);
                }
            } catch (Throwable $th) {
                $success = false;
                error($th->getMessage())->out(false);
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
     * @param bool $silent
     * @return int
     * @throws DependencyException
     * @throws InvalidDefinitionException
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
            success("success create database `{$dbName}`")->out(false);

            $this->initializeMigration();

            return 0;
        }

        error("cant created database `{$dbName}`")->out(false);

        return 1;
    }

    /**
     * @param bool $silent
     * @return int
     * @throws DependencyException
     * @throws InvalidDefinitionException
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

        $success = Schema::drop()->database($dbName)->ifExists(true)->execute();

        if ($success) {
            success("success drop database `{$dbName}`")->out(false);

            return 0;
        }

        error("cant drop database `{$dbName}`")->out(false);

        return 1;
    }

    /**
     * @throws InvalidDefinitionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function databaseShow(): int
    {
        if ($this->option('table-name')) {
            return $this->tableShow($this->option('table-name', null));
        }

        $dbName = $this->DbName();
        $width  = $this->getWidth(40, 60);
        info('showing database')->out(false);

        $tables = PDO::query('SHOW DATABASES')
            ->query('
                SELECT table_name, create_time, ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024) AS `size`
                FROM information_schema.tables
                WHERE table_schema = :db_name')
            ->bind(':db_name', $dbName)
            ->resultset();

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

    public function tableShow(string $table): int
    {
        $table = DB::table($table)->info();
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

    public function status(): int
    {
        $print = new Style();
        $print->tap(info('show migration status'));
        $width = $this->getWidth(40, 60);
        foreach ($this->getMigrationTable() as $migration_name => $batch) {
            $length = strlen($migration_name) + strlen((string) $batch);
            $print
                ->push($migration_name)
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
     * Integrate seeder during run migration.
     *
     * @return int
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    private function seed(): int
    {
        if ($this->option('dry-run', false)) {
            return 0;
        }
        if ($this->seed) {
            $seed = true === $this->seed ? null : $this->seed;

            return new SeedCommand([], ['class' => $seed])->main();
        }

        $namespace = $this->option('seed-namespace', false);
        if ($namespace) {
            $namespace = true === $namespace ? null : $namespace;

            return new SeedCommand([], ['name-space' => $namespace])->main();
        }

        return 0;
    }

    /**
     * Check for migration table exist or not in this current database.
     *
     * @return bool
     */
    private function hasMigrationTable(): bool
    {
        $result = PDO::query(
            "SELECT COUNT(table_name) as total
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            AND table_name = 'migration'"
        )->single();

        if ($result) {
            return $result['total'] > 0;
        }

        return false;
    }

    /**
     * Create migration table schema.
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
     * Get migration batch file in migration table.
     *
     * @return Collection<string, int>
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
     * Save insert migration file with batch to migration table.
     *
     * @param array<string, string|int> $migration
     * @return bool
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
     * Save insert migration file with batch to migration table.
     *
     * @param int $batchNumber
     * @return bool
     */
    private function deleteMigrationTable(int $batchNumber): bool
    {
        return DB::table('migration')
            ->delete()
            ->equal('batch', $batchNumber)
            ->execute()
            ;
    }

    /**
     * @return int
     */
    public function initializeMigration(): int
    {
        if ($this->hasMigrationTable()) {
            info('Migration table already exist on your database table.')->out(false);

            return 0;
        }

        if ($this->createMigrationTable()) {
            success('Success create migration table.')->out(false);

            return 0;
        }

        error('Migration table cant be create.')->out(false);

        return 1;
    }

    /**
     * Add migration from vendor path.
     */
    public static function addVendorMigrationPath(string $path): void
    {
        static::$vendorPaths[] = $path;
    }

    /**
     * Flush migration vendor paths.
     */
    public static function flushVendorMigrationPaths(): void
    {
        static::$vendorPaths = [];
    }
}
