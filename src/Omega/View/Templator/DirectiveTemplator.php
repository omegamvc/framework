<?php

declare(strict_types=1);

namespace Omega\View\Templator;

use Closure;
use Omega\View\AbstractTemplatorParse;
use Omega\View\Exceptions\DirectiveCanNotBeRegisterException;
use Omega\View\Exceptions\DirectiveNotRegisterException;

use function array_key_exists;
use function array_map;
use function explode;
use function implode;
use function ltrim;
use function preg_replace_callback;

class DirectiveTemplator extends AbstractTemplatorParse
{
    /**
     * @var array<string, Closure>
     */
    private static array $directive = [];

    /**
     * Excludes list of directive already use by Templator.
     *
     * @var array<string, string>
     */
    public static array $excludeList = [
        'break'    => BreakTemplator::class,
        'component' => ComponentTemplator::class,
        'continue' => ContinueTemplator::class,
        'else'     => IfTemplator::class,
        'extend'   => SectionTemplator::class,
        'foreach'  => EachTemplator::class,
        'if'       => IfTemplator::class,
        'include'  => IncludeTemplator::class,
        'bool'     => BooleanTemplator::class,
        'json'     => JsonTemplator::class,
        'php'      => PHPTemplator::class,
        'raw'      => NameTemplator::class,
        'section'  => SectionTemplator::class,
        'set'      => SetTemplator::class,
        'use'      => UseTemplator::class,
        'yield'    => SectionTemplator::class,
    ];

    /**
     * Register.
     *
     * @param string $name
     * @param Closure $callable
     * @return void
     * @throws DirectiveCanNotBeRegisterException
     */
    public static function register(string $name, Closure $callable): void
    {
        if (array_key_exists($name, self::$excludeList)) {
            throw new DirectiveCanNotBeRegisterException($name, self::$excludeList[$name]);
        }

        self::$directive[$name] = $callable;
    }

    /**
     * Call.
     *
     * @param string $name
     * @param mixed ...$parameters
     * @return string
     * @throws DirectiveCanNotBeRegisterException
     */
    public static function call(string $name, mixed ...$parameters): string
    {
        if (false === array_key_exists($name, self::$directive)) {
            throw new DirectiveNotRegisterException($name);
        }

        $callback = self::$directive[$name];

        return (string) $callback(...$parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $template): string
    {
        return preg_replace_callback(
            '/{%\s*(\w+)\((.*?)\)\s*%}/',
            function ($matches) {
                $name   = $matches[1];
                $params = explode(',', $matches[2]);
                $params = array_map(fn ($param) => ltrim($param), $params);

                return array_key_exists($name, self::$excludeList)
                    ? $matches[0]
                    : '<?php echo Omega\View\Templator\DirectiveTemplator::call(\''
                    . $name . '\', '
                    . implode(', ', $params) . '); ?>'
                ;
            },
            $template
        );
    }
}
