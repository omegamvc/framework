<?php

/**
 * Part of Omega - View Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\View\ServiceProvider;

use Omega\Application\Application;
use OMega\Cache\Item\Item;
use Omega\Container\Contracts\ServiceProvider\ServiceProviderInterface;
use Omega\Support\Facade\Facades\View;
use Omega\View\ViewManager;
use Omega\View\Engine\AdvancedEngine;
use Omega\View\Engine\BasicEngine;
use Omega\View\Engine\LiteralEngine;
use Omega\View\Engine\PhpEngine;

use function htmlspecialchars;

use const ENT_QUOTES;

/**
 * View service provider class.
 *
 * The `ViewServiceProvider` class provides service bindings related to the View
 * component in the Omega system. It binds the 'view' service, which provides
 * access to the ViewManager for rendering views.
 *
 * @category   Omega
 * @package    View
 * @subpackage ServiceProvider
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class ViewServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function bind(Application $application): void
    {
        $application->alias('view', function ($application) {
            $viewManager = new ViewManager();

            $this->bindPaths($application, $viewManager);
            $this->bindMacros($application, $viewManager);
            $this->bindEngine($application, $viewManager);

            return $viewManager;
        });
    }

    /**
     * Bind the view paths.
     *
     * Adds view paths to the ViewManager to specify where views can be located.
     *
     * @param Application $application Holds an instance of Application.
     * @param ViewManager $viewManager Holds an instance of ViewManager.
     *
     * @return void
     */
    private function bindPaths(Application $application, ViewManager $viewManager): void
    {
        $viewManager->addPath($application->resolve('paths.base') . '/resources/views');
        $viewManager->addPath($application->resolve('paths.base') . '/resources/images');
    }

    /**
     * Bind view macros.
     *
     * Adds macros to the ViewManager for use in view templates.
     *
     * @param Application $application Holds an instance of Application.
     * @param ViewManager $viewManager Holds an instance of ViewManager.
     *
     * @return void
     */
    private function bindMacros(Application $application, ViewManager $viewManager): void
    {
        /**$viewManager->addMacro('escape', fn($value) => @htmlspecialchars(
            $value instanceof Item ? $value->get() : $value,
            ENT_QUOTES
        ));*/
        $viewManager->addMacro('escape', function ($value) {
            //error_log('Value in escape macro: ' . var_export($value, true));
            return $value !== null ? @htmlspecialchars(
                $value instanceof Item ? $value->get() : $value,
                ENT_QUOTES
            ) : '';
        });
        $viewManager->addMacro('includes', fn(...$params) => print View::render(...$params));
    }

    /**
     * Bind view renderers.
     *
     * Adds various renderers to the ViewManager based on file extensions.
     *
     * @param Application $application Holds an instance of Application.
     * @param ViewManager $viewManager Holds an instance of ViewManager.
     *
     * @return void
     */
    private function bindEngine(Application $application, ViewManager $viewManager): void
    {
        $application->alias('view.engine.basic', fn() => new BasicEngine());
        $application->alias('view.engine.nexus', fn() => new AdvancedEngine());
        $application->alias('view.engine.php', fn() => new PhpEngine());
        $application->alias('view.engine.literal', fn() => new LiteralEngine());

        $viewManager->addEngine('basic.php', $application->resolve('view.engine.basic'));
        $viewManager->addEngine('nexus.php', $application->resolve('view.engine.nexus'));
        $viewManager->addEngine('php', $application->resolve('view.engine.php'));
        $viewManager->addEngine('svg', $application->resolve('view.engine.literal'));
    }
}
