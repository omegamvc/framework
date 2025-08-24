<?php

declare(strict_types=1);

namespace Omega\Console\Commands;

use Omega\Console\Command;
use Omega\Console\Traits\CommandTrait;
use Omega\Support\Facades\DB;
use Omega\Template\Generate;
use Omega\Template\Property;

use function Omega\Console\fail;
use function Omega\Console\info;
use function Omega\Console\ok;
use function Omega\Console\text;
use function Omega\Console\warn;

/**
 * @property bool $update
 * @property bool $force
 */
class MakeCommand extends Command
{
    use CommandTrait;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'make:controller',
            'fn'      => [MakeCommand::class, 'make_controller'],
        ], [
            'pattern' => 'make:view',
            'fn'      => [MakeCommand::class, 'make_view'],
        ], [
            'pattern' => 'make:services',
            'fn'      => [MakeCommand::class, 'make_services'],
        ], [
            'pattern' => 'make:model',
            'fn'      => [MakeCommand::class, 'make_model'],
        ], [
            'pattern' => 'make:command',
            'fn'      => [MakeCommand::class, 'make_command'],
        ], [
            'pattern' => 'make:migration',
            'fn'      => [MakeCommand::class, 'make_migration'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp()
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

    public function make_controller(): int
    {
        info('Making controller file...')->out(false);

        $success = $this->makeTemplate($this->option[0], [
            'template_location' => __DIR__ . '/stubs/controller',
            'save_location'     => get_path('path.controller'),
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

    public function make_view(): int
    {
        info('Making view file...')->out(false);

        $success = $this->makeTemplate($this->option[0], [
            'template_location' => __DIR__ . '/stubs/view',
            'save_location'     => get_path('path.view'),
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

    public function make_services(): int
    {
        info('Making service file...')->out(false);

        $success = $this->makeTemplate($this->option[0], [
            'template_location' => __DIR__ . '/stubs/service',
            'save_location'     => get_path('path.services'),
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

    public function make_model(): int
    {
        info('Making model file...')->out(false);
        $name           = ucfirst($this->option[0]);
        $model_location = get_path('path.model') . $name . '.php';

        if (file_exists($model_location) && false === $this->option('force', false)) {
            warn('File already exist')->out(false);
            fail('Failed Create model file')->out();

            return 1;
        }

        info('Creating Model class in ' . $model_location)->out(false);

        $class = new Generate($name);
        $class->customizeTemplate("<?php\n\ndeclare(strict_types=1);\n{{before}}{{comment}}\n{{rule}}class\40{{head}}\n{\n{{body}}}{{end}}");
        $class->tabSize(4);
        $class->tabIndent(' ');
        $class->setEndWithNewLine();
        $class->namespace('App\\Models');
        $class->uses(['Omega\Database\MyModel\Model']);
        $class->extend('Model');

        $primery_key = 'id';
        $table_name  = $this->option[0];
        if ($this->option('table-name', false)) {
            $table_name = $this->option('table-name');
            info("Getting Information from table {$table_name}.")->out(false);
            try {
                foreach (DB::table($table_name)->info() as $column) {
                    $class->addComment('@property mixed $' . $column['COLUMN_NAME']);
                    if ('PRI' === $column['COLUMN_KEY']) {
                        $primery_key = $column['COLUMN_NAME'];
                    }
                }
            } catch (\Throwable $th) {
                warn($th->getMessage())->out(false);
            }
        }

        $class->addProperty('table_name')->visibility(Property::PROTECTED_)->dataType('string')->expecting(" = '{$table_name}'");
        $class->addProperty('primery_key')->visibility(Property::PROTECTED_)->dataType('string')->expecting("= '{$primery_key}'");

        if (false === file_put_contents($model_location, $class->generate())) {
            fail('Failed Create model file')->out();

            return 1;
        }

        ok("Finish created model file `App\\Models\\{$name}`")->out();

        return 0;
    }

    /**
     * Replece template to new class/resoure.
     *
     * @param string                $argument    Name of Class/file
     * @param array<string, string> $make_option Configuration to replace template
     * @param string                $folder      Create folder for save location
     *
     * @return bool True if templete success copie
     */
    private function makeTemplate(string $argument, array $make_option, string $folder = ''): bool
    {
        $folder = ucfirst($folder);
        if (file_exists($file_name = $make_option['save_location'] . $folder . $argument . $make_option['suffix'])) {
            warn('File already exist')->out(false);

            return false;
        }

        if ('' !== $folder && !is_dir($make_option['save_location'] . $folder)) {
            mkdir($make_option['save_location'] . $folder);
        }

        $get_template = file_get_contents($make_option['template_location']);
        $get_template = str_replace($make_option['pattern'], ucfirst($argument), $get_template);
        $get_template = preg_replace('/^.+\n/', '', $get_template);
        $isCopied     = file_put_contents($file_name, $get_template);

        return $isCopied === false ? false : true;
    }

    public function make_command(): int
    {
        info('Making command file...')->out(false);
        $name    = $this->option[0];
        $success = $this->makeTemplate($name, [
            'template_location' => __DIR__ . '/stubs/command',
            'save_location'     => get_path('path.command'),
            'pattern'           => '__command__',
            'suffix'            => 'Command.php',
        ]);

        if ($success) {
            $getContent = file_get_contents(get_path('path.config') . 'command.php');
            $getContent = str_replace(
                '// more command here',
                "// {$name} \n\t" . 'App\\Commands\\' . $name . 'Command::$' . "command\n\t// more command here",
                $getContent
            );

            file_put_contents(get_path('path.config') . 'command.php', $getContent);

            ok('Finish created command file')->out();

            return 0;
        }

        fail("\nFailed Create command file")->out();

        return 1;
    }

    public function make_migration(): int
    {
        info('Making migration')->out(false);

        $name = $this->option[0] ?? false;
        if (false === $name) {
            warn('Table name cant be empty.')->out(false);
            do {
                $name = text('Fill the table name?', static fn ($text) => $text);
            } while ($name === '' || $name === false);
        }

        $name         = strtolower($name);
        $path_to_file = get_path('path.migration');
        $bath         = now()->format('Y_m_d_His');
        $file_name    = "{$path_to_file}{$bath}_{$name}.php";

        $use      = $this->update ? 'migration_update' : 'migration';
        $template = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $use);
        $template = str_replace('__table__', $name, $template);

        if (false === file_exists($path_to_file) || false === file_put_contents($file_name, $template)) {
            fail('Can\'t create migration file.')->out();

            return 1;
        }
        ok('Success create migration file.')->out();

        return 0;
    }
}
