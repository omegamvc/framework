<?php

/**
 * Part of Omega CMS - Renderer Package.
 *
 * @see        https://omegacms.github.io
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\View\Engine;

/*
 * @use
 */
use Omega\View\ViewManager;

/**
 * Has manager trait class.
 *
 * The `HasManagerTrait` provides a common method to set a ViewManager object. It is used
 * to manage view-related functionality within rendering classes.
 *
 * @category    Omega
 * @package     View
 * @subpackage  Engine
 *
 * @see        https://omegacms.github.io
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
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
     *
     * @return $this
     */
    public function setManager(ViewManager $viewManager): static
    {
        $this->viewManager = $viewManager;

        return $this;
    }
}
