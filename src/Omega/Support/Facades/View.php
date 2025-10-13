<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\View\Templator;
use Omega\View\TemplatorFinder;

/**
 * @method static Templator          setFinder(TemplatorFinder $finder)
 * @method static Templator          setComponentNamespace(string $namespace)
 * @method static Templator          addDependency(string $parent, string $child, int $dependDeep = 1)
 * @method static Templator          prependDependency(string $parent, array<string, int> $children)
 * @method static array<string, int> getDependency(string $parent)
 * @method static string             render(array<string, mixed> $data, string $templateName, bool $cache = true)
 * @method static string             compile(string $templateName)
 * @method static bool               viewExist(string $templateName)
 * @method static string             templates(string $template, string $viewLocation = '')
 *
 * @see Templator
 */
final class View extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'view.instance';
    }
}
