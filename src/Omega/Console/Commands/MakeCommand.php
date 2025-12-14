<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

declare(strict_types=1);

namespace Omega\Console\Commands;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use Exception;
use Omega\Console\AbstractCommand;
use Omega\Console\Style\Style;
use Omega\Console\Traits\CommandTrait;
use Omega\Container\Exceptions\BindingResolutionException;
use Omega\Container\Exceptions\CircularAliasException;
use Omega\Container\Exceptions\EntryNotFoundException;
use Omega\Support\Facades\DB;
use Omega\Template\Generate;
use Omega\Template\Property;
use ReflectionException;
use Throwable;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function Omega\Console\error;
use function Omega\Console\info;
use function Omega\Console\success;
use function Omega\Console\text;
use function Omega\Console\warn;
use function Omega\Time\now;
use function preg_replace;
use function str_replace;
use function strtolower;
use function ucfirst;

/**
 * @property bool $update
 * @property bool $force
 */
class MakeCommand extends AbstractCommand
{
    use CommandTrait;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'make:command',
            'fn'      => [MakeCommand::class, 'make_command'],
        ], [
            'pattern' => 'make:controller',
            'fn'      => [MakeCommand::class, 'make_controller'],
        ], [
            'pattern' => 'make:exception',
            'fn'      => [MakeCommand::class, 'make_exception'],
        ], [
            'pattern' => 'make:middleware',
            'fn'      => [MakeCommand::class, 'make_middleware'],
        ], [
            'pattern' => 'make:migration',
            'fn'      => [MakeCommand::class, 'make_migration'],
        ], [
            'pattern' => 'make:model',
            'fn'      => [MakeCommand::class, 'make_model'],
        ], [
            'pattern' => 'make:provider',
            'fn'      => [MakeCommand::class, 'make_provider'],
        ], [
            'pattern' => 'make:view',
            'fn'      => [MakeCommand::class, 'make_view'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'make:command'    => 'Generate new command class',
                'make:controller' => 'Generate new controller class',
                'make:exception'  => 'Generate new exception class',
                'make:middleware' => 'Generate new middleware class',
                'make:migration'  => 'Generate new migration file',
                'make:model'      => 'Generate new model class',
                'make:provider'   => 'Generate new service provider class',
                'make:view'       => 'Generate new view template',
            ],
            'options'   => [
                '--table-name' => 'Set table column when creating model.',
                '--update'     => 'Generate migration file with alter (update).',
                '--force'      => 'Force to creating template.',
            ],
            'relation'  => [
                'make:command'    => ['[command_name]'],
                'make:controller' => ['[controller_name]'],
                'make:exception'  => ['[exception_name]'],
                'make:middleware' => ['[middleware_name]'],
                'make:migration'  => ['[table_name]', '--update'],
                'make:model'      => ['[model_name]', '--table-name', '--force'],
                'make:provider'   => ['[provider_name]'],
                'make:view'       => ['[view_name]'],
            ],
        ];
    }

    /**
     * @return int
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function make_controller(): int
    {
        info('Making controller file...')->out(false);

        $this->isPath('path.controller');

        $success = $this->makeTemplate($this->option[0], [
            'template_location' => __DIR__ . '/stubs/controller',
            'save_location'     => get_path('path.controller'),
            'pattern'           => '__controller__',
            'suffix'            => 'Controller.php',
        ]);

        $path = path('app.Http.Controllers') . $this->option[0] . 'Controller.php';

        if ($success) {
            success('Controller [' . new Style($path)->bold() . '] created successfully.')->out();

            return 0;
        }

        error('Failed Create controller')->out();

        return 1;
    }

    /**
     * @return int
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function make_middleware(): int
    {
        info('Making middleware file...')->out(false);

        $this->isPath('path.middleware');

        $success = $this->makeTemplate($this->option[0], [
            'template_location' => __DIR__ . '/stubs/middleware',
            'save_location'     => get_path('path.middleware'),
            'pattern'           => '__middleware__',
            'suffix'            => 'Middleware.php',
        ]);

        $path = path('app.Http.Middlewares') . $this->option[0] . 'Middleware.php';

        if ($success) {
            success('Middleware [' . new Style($path)->bold() . '] created successfully.')->out();

            return 0;
        }

        error('Failed create middleware.')->out();

        return 1;
    }

    /**
     * @return int
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function make_exception(): int
    {
        info('Making exception file...')->out(false);

        $this->isPath('path.exception');

        $success = $this->makeTemplate($this->option[0], [
            'template_location' => __DIR__ . '/stubs/exception',
            'save_location'     => get_path('path.exception'),
            'pattern'           => '__exception__',
            'suffix'            => 'Exception.php',
        ]);

        $path = path('app.Exceptions') . $this->option[0] . 'Exception.php';

        if ($success) {
            success('Exception [' . new Style($path)->bold() . '] created successfully.')->out();

            return 0;
        }

        error('Failed Create controller')->out();

        return 1;
    }

    /**
     * @return int
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
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
            success('Finish created view file')->out();

            return 0;
        }

        error('Failed Create view file')->out();

        return 1;
    }

    /**
     * @return int
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function make_provider(): int
    {
        info('Making service provider file...')->out(false);

        $this->isPath('path.provider');

        $success = $this->makeTemplate($this->option[0], [
            'template_location' => __DIR__ . '/stubs/provider',
            'save_location'     => get_path('path.provider'),
            'pattern'           => '__provider__',
            'suffix'            => 'ServiceProvider.php',
        ]);

        $path = path('app.Http.Providers') . $this->option[0] . 'Provider.php';

        if ($success) {
            success('Provider [' . new Style($path)->bold() . '] created successfully.')->out();

            return 0;
        }

        error('Failed Create services file')->out();

        return 1;
    }

    /**
     * @return int
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function make_model(): int
    {
        info('Making model file...')->out(false);

        $this->isPath('path.model');

        $name          = ucfirst($this->option[0]);
        $modelLocation = get_path('path.model') . $name . '.php';

        if (file_exists($modelLocation) && false === $this->option('force', false)) {
            warn('File already exist')->out(false);
            error('Failed Create model file')->out();

            return 1;
        }

        info('Creating Model class in ' . $modelLocation)->out(false);

        $class = new Generate($name);
        $class->customizeTemplate(
            "<?php\n\ndeclare(strict_types=1);\n{{before}}{{comment}}\n{{rule}}class\40{{head}}\n{\n{{body}}}{{end}}"
        );
        $class->tabSize(4);
        $class->tabIndent(' ');
        $class->setEndWithNewLine();
        $class->namespace('App\\Models');
        $class->uses(['Omega\Database\Model\Model']);
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

        $class->addProperty('tableName')
            ->visibility(Property::PROTECTED_)
            ->dataType('string')
            ->expecting(" = '{$tableName}'");
        $class->addProperty('primaryKey')
            ->visibility(Property::PROTECTED_)
            ->dataType('string')
            ->expecting("= '{$primaryKey}'");

        if (false === file_put_contents($modelLocation, $class->generate())) {
            error('Failed Create model file')->out();

            return 1;
        }

        $path = path('app.Models') . $name;

        success('Model [' . new Style($path)->bold() . '] create successfully.')->out();

        return 0;
    }

    /**
     * Replace template to new class/resource.
     *
     * @param string                $argument   Name of Class/file
     * @param array<string, string> $makeOption Configuration to replace template
     * @param string                $folder     Create folder for save location
     *
     * @return bool True if template success copied
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
     * @return int
     * @throws BindingResolutionException Thrown when resolving a binding fails.
     * @throws CircularAliasException Thrown when alias resolution loops recursively.
     * @throws EntryNotFoundException Thrown when no entry exists for the identifier.
     * @throws ReflectionException Thrown when the requested class or interface cannot be reflected.
     */
    public function make_command(): int
    {
        info('Making command file...')->out(false);

        $this->isPath('path.command');

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

            $path = path('app.Console.Commands') . $name . 'Command.php';

            success('Command [' . new Style($path)->bold() . '] create successfully.')->out();

            return 0;
        }

        error("\nFailed Create command file")->out();

        return 1;
    }

    /**
     * @return int
     * @throws DateInvalidTimeZoneException Thrown when a provided timezone is invalid.
     * @throws DateMalformedStringException Thrown when a date string cannot be parsed correctly.
     * @throws Exception
     */
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

        $name       = strtolower($name);
        $pathToFile = get_path('path.migration');
        $bath       = now()->format('Y_m_d_His');
        $fileName   = "{$pathToFile}{$bath}_{$name}.php";

        $use      = $this->update ? 'migration_update' : 'migration';
        $template = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $use);
        $template = str_replace('__table__', $name, $template);

        if (false === file_exists($pathToFile) || false === file_put_contents($fileName, $template)) {
            error('Can\'t create migration file.')->out();

            return 1;
        }
        success('Success create migration file.')->out();

        return 0;
    }
}
