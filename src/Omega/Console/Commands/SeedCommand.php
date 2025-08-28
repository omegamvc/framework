<?php /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

declare(strict_types=1);

namespace Omega\Console\Commands;

use Exception;
use Omega\Console\AbstractCommand;
use Omega\Console\Prompt;
use Omega\Console\Traits\PrintHelpTrait;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Template\Generate;
use Omega\Template\Method;
use Throwable;
use function class_exists;
use function file_exists;
use function file_put_contents;
use function Omega\Console\error;
use function Omega\Console\info;
use function Omega\Console\style;
use function Omega\Console\success;
use function Omega\Console\warn;

/**
 * @property string|null $class
 * @property bool|null   $force
 */
class SeedCommand extends AbstractCommand
{
    use PrintHelpTrait;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'db:seed',
            'fn'      => [self::class, 'main'],
        ], [
            'pattern' => 'make:seed',
            'fn'      => [self::class, 'make'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'db:seed'      => 'Run seeding',
                'make:seed'    => 'Create new seeder class',
            ],
            'options'   => [
                '--class'      => 'Target class (will add `Database\\Seeders\\`)',
                '--name-space' => 'Target class with full namespace',
            ],
            'relation'  => [
                'db:seed'      => ['--class', '--name-space'],
            ],
        ];
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
        return new Prompt(style('Running seeder in production?')->textRed(), [
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
     * @throws InvalidDefinitionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function main(): int
    {
        $class     = $this->class;
        $namespace = $this->option('name-space');
        $exit      = 0;

        if (false === $this->runInDev()) {
            return 2;
        }

        if (null !== $class && null !== $namespace) {
            warn('Use only one class or namespace, be specific.')->out(false);

            return 1;
        }

        if (null === $class && null !== $namespace) {
            $class = $namespace;
        }

        if ($class !== null && null === $namespace) {
            $class = 'Database\\Seeders\\' . $class;
        }

        if (null === $class && null === $namespace) {
            $class = 'Database\\Seeders\\DatabaseSeeder';
        }

        if (false === class_exists($class)) {
            warn("Class '{$class}::class' doest exist.")->out(false);

            return 1;
        }

        info('Running seeders...')->out(false);
        try {
            app()->call([$class, 'run']);

            success('Success run seeder ' . $class)->out(false);
        } catch (Throwable $th) {
            warn($th->getMessage())->out(false);
            $exit = 1;
        }

        return $exit;
    }

    /**
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     * @throws DependencyException
     */
    public function make(): int
    {
        $class = $this->option[0] ?? null;

        if (null === $class) {
            warn('command make:seed require class name')->out(false);

            return 1;
        }

        if (file_exists(get_path('path.seeder', $class . '.php')) && !$this->force) {
            warn("Class '{$class}::class' already exist.")->out(false);

            return 1;
        }

        $make = new Generate($class);
        $make->tabIndent(' ');
        $make->tabSize(4);
        $make->namespace('Database\Seeders');
        $make->use('Omega\Database\Seeder\Seeder');
        $make->extend('Seeder');
        $make->setEndWithNewLine();
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        $make->addMethod('run')
            ->visibility(Method::PUBLIC_)
            ->setReturnType('void')
            ->body('// run some insert db');

        if (file_put_contents(get_path('path.seeder', $class . '.php'), $make->__toString())) {
            success('Success create seeder')->out();

            return 0;
        }

        error('Fail to create seeder')->out();

        return 1;
    }
}
