<?php

/**
 * Part of Omega - Renderer Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\View\Engine;

use Omega\View\View;
use Omega\View\ViewManager;

/**
 * Engine interface.
 *
 * The `RendererInterface` defines the contract for rendering views in the Omega
 * system. Classes implementing this interface must provide a way to render a View
 * object and allow setting the ViewManager to be used for rendering.
 *
 * @category   Omega
 * @package    View
 * @subpackage Engine
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
interface EngineInterface
{
    /**
     * Render a view.
     *
     * This method is responsible for rendering a View object and processing its contents.
     *
     * @param View $view Holds an instance of View.
     * @return string Return the view.
     */
    public function render(View $view): string;

    /**
     * Set the view manager to use.
     *
     * @param ViewManager $viewManager Holds an instance of ViewManager.
     * @return $this
     */
    public function setManager(ViewManager $viewManager): static;
}
