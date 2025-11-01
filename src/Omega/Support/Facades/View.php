<?php

/**
 * Part of Omega - Facades Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\View\Templator;
use Omega\View\TemplatorFinder;

/**
 * Facade for the View service.
 *
 * This facade provides a static interface to the underlying `View` instance
 * resolved from the application container. It allows convenient static-style
 * calls while still relying on dependency injection and the container under the hood.
 *
 * Usage of this facade does not create a global state; the underlying instance
 * is still managed by the container and may be swapped, mocked, or replaced
 * for testing or customization purposes.
 *
 * @category   Omega
 * @package    Support
 * @subpackges Facades
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
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
    /**
     * {@inheritdoc}
     */
    public static function getFacadeAccessor(): string
    {
        return 'view.instance';
    }
}
