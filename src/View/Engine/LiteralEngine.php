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
use function file_get_contents;
use Omega\View\View;

/**
 * Literal engine class.
 *
 * TThe `LiteralRenderer` provides a renderer that treats the view as a literal file.
 * It directly returns the contents of the view file without any processing.
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
class LiteralEngine extends AbstractEngine
{
    /**
     * {@inheritdoc}
     *
     * @param View $view Holds an instance of View.
     *
     * @return string Return the view.
     */
    public function render(View $view): string
    {
        return file_get_contents($view->path);
    }
}
