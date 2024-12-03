<?php

/**
 * Part of Omega CMS - Renderer Package.
 *
 * @see       https://omegacms.github.io
 *
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
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
use function file_get_contents;
use function str_replace;
use Omega\View\View;

/**
 * Basic engine class.
 *
 * @category    Omega
 * @package     View
 * @subpackage  Engine
 *
 * @see        https://omegacms.github.io1
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class BasicEngine extends AbstractEngine
{
    /**
     * {@inheritdoc}
     *
     * This method is responsible for rendering a View object and processing its contents.
     *
     * @param View $view Holds an instance of View.
     *
     * @return string Return the view.
     */
    public function render(View $view): string
    {
        $contents = file_get_contents($view->path);

        foreach ($view->data as $key => $value) {
            $contents = str_replace(
                '{' . $key . '}',
                $value,
                $contents
            );
        }

        return $contents;
    }
}
