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

use Exception;
use Omega\Console\Command;
use Omega\Console\Traits\CommandTrait;
use Omega\Support\Facades\DB;
use Omega\Template\Generate;
use Omega\Template\Property;
use Throwable;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function Omega\Console\fail;
use function Omega\Console\info;
use function Omega\Console\ok;
use function Omega\Console\text;
use function Omega\Console\warn;
use function Omega\Time\now;
use function preg_replace;
use function str_replace;
use function strtolower;
use function ucfirst;

/**
 * Class MakeCommand
 *
 * This command class handles the generation of various application components
 * such as controllers, views, services, models, commands, and migrations.
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
 * @property bool $update
 * @property bool $force
 */
class MakeCommand extends Command
{
    use CommandTrait;

    /**
     * Command registration list.
     *
     * Each entry defines the command pattern and its corresponding handler method.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'make:controller',
            'fn'      => [MakeCommand::class, 'makeController'],
        ], [
            'pattern' => 'make:view',
            'fn'      => [MakeCommand::class, 'makeView'],
        ], [
            'pattern' => 'make:services',
            'fn'      => [MakeCommand::class, 'makeServices'],
        ], [
            'pattern' => 'make:model',
            'fn'      => [MakeCommand::class, 'makeModel'],
        ], [
            'pattern' => 'make:command',
            'fn'      => [MakeCommand::class, 'makeCommand'],
        ], [
            'pattern' => 'make:migration',
            'fn'      => [MakeCommand::class, 'makeMigration'],
        ],
    ];

    /**
     * Return help information for available commands, options, and their usage.
     *
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'make:controller' => 'Generate new controller',
                'make:view'       => 'Generate new view',
                'make:service'    => 'Generate new service',
                'make:model'      => 'Generate new model',
                'make:command'    => 'Generate new command',
                'make:migration'  => 'Generate new migration file',
            ],
            'options'   => [
                '--table-name' => 'Set table column when creating model.',
                '--update'     => 'Generate migration file with alter (update).',
                '--force'      => 'Force to creating template.',
            ],
            'relation'  => [
                'make:controller' => ['[controller_name]'],
                'make:view'       => ['[view_name]'],
                'make:service'    => ['[service_name]'],
                'make:model'      => ['[model_name]', '--table-name', '--force'],
                'make:command'    => ['[command_name]'],
                'make:migration'  => ['[table_name]', '--update'],
            ],
        ];
    }

    /**
     * Generate a new controller class.
     *
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function makeController(): int
    {
        info('Making controller file...')->out(false);

        $success = $this->makeTemplate($this->option[0], [
            'template_location' => __DIR__ . '/stubs/controller',
            'save_location'     => controllers_path(),
            'pattern'           => '__controller__',
            'suffix'            => 'Controller.php',
        ]);

        if ($success) {
            ok('Finish created controller')->out();

            return 0;
        }

        fail('Failed Create controller')->out();

        return 1;
    }

    /**
     * Generate a new view template file.
     *
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function makeView(): int
    {
        info('Making view file...')->out(false);

        $success = $this->makeTemplate($this->option[0], [
            'template_location' => __DIR__ . '/stubs/view',
            'save_location'     => view_path(),
            'pattern'           => '__view__',
            'suffix'            => '.template.php',
        ]);

        if ($success) {
            ok('Finish created view file')->out();

            return 0;
        }

        fail('Failed Create view file')->out();

        return 1;
    }

    /**
     * Generate a new service class.
     *
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function makeServices(): int
    {
        info('Making service file...')->out(false);

        $success = $this->makeTemplate($this->option[0], [
            'template_location' => __DIR__ . '/stubs/service',
            'save_location'     => services_path(),
            'pattern'           => '__service__',
            'suffix'            => 'Service.php',
        ]);

        if ($success) {
            ok('Finish created services file')->out();

            return 0;
        }

        fail('Failed Create services file')->out();

        return 1;
    }

    /**
     * Generate a new model class file, optionally extracting properties
     * from the corresponding database table if the --table-name option is used.
     *
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function makeModel(): int
    {
        info('Making model file...')->out(false);
        $name           = ucfirst($this->option[0]);
        $modelLocation  = model_path() . $name . '.php';

        if (file_exists($modelLocation) && false === $this->option('force', false)) {
            warn('File already exist')->out(false);
            fail('Failed Create model file')->out();

            return 1;
        }

        info('Creating Model class in ' . $modelLocation)->out(false);

        $class = new Generate($name);
        $class->customizeTemplate("<?php\n\ndeclare(strict_types=1);\n{{before}}{{comment}}\n{{rule}}class\40{{head}}\n{\n{{body}}}{{end}}");
        $class->tabSize(4);
        $class->tabIndent(' ');
        $class->setEndWithNewLine();
        $class->namespace('App\\Models');
        $class->uses(['Omega\Database\Model\Model\Model']);
        $class->extend('Model');

        $primaryKey = 'id';
        $tableName  = $this->option[0];
        if ($this->option('table-name', false)) {
            $tableName = $this->option('table-name');
            info("Getting Information from table {$tableName}.")->out(false);
            try {
                foreach (DB::table($tableName)->info() as $column) {
                    $class->addComment('@property mixed $' . $column['COLUMN_NAME']);
                    if ('PRI' === $column['COLUMN_KEY']) {
                        $primaryKey = $column['COLUMN_NAME'];
                    }
                }
            } catch (Throwable $th) {
                warn($th->getMessage())->out(false);
            }
        }

        $class->addProperty('table_name')->visibility(Property::PROTECTED_)->dataType('string')->expecting(" = '{$tableName}'");
        $class->addProperty('primary_key')->visibility(Property::PROTECTED_)->dataType('string')->expecting("= '{$primaryKey}'");

        if (false === file_put_contents($modelLocation, $class->generate())) {
            fail('Failed Create model file')->out();

            return 1;
        }

        ok("Finish created model file `App\\Models\\{$name}`")->out();

        return 0;
    }

    /**
     * Replace template with a new class/resource.
     *
     * This method handles the creation of a file from a stub template,
     * applying naming replacements and optionally creating folders.
     *
     * @param string                $argument    Name of the class/file to generate
     * @param array<string, string> $makeOption Configuration for template replacement
     * @param string                $folder      Optional subfolder under save_location
     *
     * @return bool True if template was successfully copied and written
     */
    private function makeTemplate(string $argument, array $makeOption, string $folder = ''): bool
    {
        $folder = ucfirst($folder);
        if (file_exists($fileName = $makeOption['save_location'] . $folder . $argument . $makeOption['suffix'])) {
            warn('File already exist')->out(false);

            return false;
        }

        if ('' !== $folder && !is_dir($makeOption['save_location'] . $folder)) {
            mkdir($makeOption['save_location'] . $folder);
        }

        $getTemplate = file_get_contents($makeOption['template_location']);
        $getTemplate = str_replace($makeOption['pattern'], ucfirst($argument), $getTemplate);
        $getTemplate = preg_replace('/^.+\n/', '', $getTemplate);
        $isCopied    = file_put_contents($fileName, $getTemplate);

        return !($isCopied === false);
    }

    /**
     * Generate a new console command class and register it in the config.
     *
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function makeCommand(): int
    {
        info('Making command file...')->out(false);
        $name    = $this->option[0];
        $success = $this->makeTemplate($name, [
            'template_location' => __DIR__ . '/stubs/command',
            'save_location'     => commands_path(),
            'pattern'           => '__command__',
            'suffix'            => 'Command.php',
        ]);

        if ($success) {
            $geContent = file_get_contents(config_path() . 'command.php');
            $geContent = str_replace(
                '// more command here',
                "// {$name} \n\t" . 'App\\Commands\\' . $name . 'Command::$' . "command\n\t// more command here",
                $geContent
            );

            file_put_contents(config_path() . 'command.php', $geContent);

            ok('Finish created command file')->out();

            return 0;
        }

        fail("\nFailed Create command file")->out();

        return 1;
    }

    /**
     * Generate a new migration file based on a table name and type (create/update).
     *
     * @throws Exception
     *
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function makeMigration(): int
    {
        info('Making migration')->out(false);

        $name = $this->option[0] ?? false;
        if (false === $name) {
            warn('Table name cant be empty.')->out(false);
            do {
                $name = text('Fill the table name?', static fn ($text) => $text);
            } while ($name === '' || $name === false);
        }

        $name       = strtolower($name);
        $pathToFile = migration_path();
        $bath       = now()->format('Y_m_d_His');
        $fileName   = "{$pathToFile}{$bath}_{$name}.php";

        $use      = $this->update ? 'migration_update' : 'migration';
        $template = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $use);
        $template = str_replace('__table__', $name, $template);

        if (false === file_exists($pathToFile) || false === file_put_contents($fileName, $template)) {
            fail('Can\'tests create migration file.')->out();

            return 1;
        }
        ok('Success create migration file.')->out();

        return 0;
    }
}
