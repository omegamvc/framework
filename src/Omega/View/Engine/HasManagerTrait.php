<?php

/**
 * Part of Omega - Renderer Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\View\Engine;

use Omega\View\ViewManager;

/**
 * Has manager trait class.
 *
 * The `HasManagerTrait` provides a common method to set a ViewManager object. It is used
 * to manage view-related functionality within rendering classes.
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
trait HasManagerTrait
{
    /**
     * ViewManager object.
     *
     * @var ViewManager Holds an instance of ViewManager.
     */
    protected ViewManager $viewManager;

    /**
     * Set the view manager object.
     *
     * @param ViewManager $viewManager Holds an instance of ViewManager.
     * @return $this
     */
    public function setManager(ViewManager $viewManager): static
    {
        $this->viewManager = $viewManager;

        return $this;
    }
}
