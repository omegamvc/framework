<?php

/**
 * Part of Omega - Renderer Package.
 *
 * @see       https://omegamvc.github.io
 *
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
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
use function array_merge;
use Omega\Support\Facade\Facades\View;

/**
 * Php engine class.
 *
 * @category    Omega
 * @package     View
 * @subpackage  Engine
 *
 * @see        https://omegamvc.github.io1
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class PhpEngine extends AbstractEngine
{
    /**
     * {@inheritdoc}
     *
     * This method is responsible for rendering a View object and processing its contents.
     *
     * @param \Omega\View\View $view Holds an instance of View.
     *
     * @return string Return the view.
     */
    public function render(\Omega\View\View $view): string
    {
        extract($view->data);

        ob_start();
        include $view->path;
        $contents = ob_get_contents();
        ob_end_clean();

        if ($layout = $this->layouts[$view->path] ?? null) {
            $contentsWithLayout = View::render($layout, array_merge(
                $view->data,
                [ 'contents' => $contents ],
            ));

            return $contentsWithLayout;
        }

        return $contents;
    }
}
