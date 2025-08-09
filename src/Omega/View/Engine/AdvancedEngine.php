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

use Omega\Support\Facade\Facades\View;
use Omega\Support\Path;

use function array_merge;
use function extract;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function is_file;
use function md5;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function preg_replace_callback;
use function realpath;
use function touch;

/**
 * Advanced engine class.
 *
 * The `AdvancedEngine` class is part of the Omega Renderer Package and
 * provides advanced rendering capabilities.
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
class AdvancedEngine extends AbstractEngine
{
    /**
     * {@inheritdoc}
     */
    public function render(\Omega\View\View $view): string
    {
        $hash   = md5($view->path);
        $folder = Path::getPath('storage', 'framework/data/views');

        if (! is_file("{$folder}/{$hash}.php")) {
            touch("{$folder}/{$hash}.php");
        }

        $cached = realpath("{$folder}/{$hash}.php");

        if (! file_exists($hash) || filemtime($view->path) > filemtime($hash)) {
            $content = $this->compile(file_get_contents($view->path));
            file_put_contents($cached, $content);
        }

        extract($view->data);

        ob_start();
        include $cached;
        $contents = ob_get_contents();
        ob_end_clean();

        if ($layout = $this->layouts[$cached] ?? null) {
            $layoutView = View::render($layout, array_merge(
                $view->data,
                [ 'contents' => $contents ],
            ));

            return $layoutView->__toString();
        }

        return $contents;
    }

    /**
     * Compile the template.
     *
     * This method compiles the template content by processing various directives
     * and constructs.
     *
     * @param string $template Holds the template name.
     * @return string Return the compiled template.
     */
    protected function compile(string $template): string
    {
        // replace `@extends` with `$this->extends`
        $template = preg_replace_callback('#@extends\(((?<=\().*(?=\)))\)#', function ($matches) {
            return '<?php $this->extends(' . $matches[1] . '); ?>';
        }, $template);

        // replace `@if` with `if(...):`
        $template = preg_replace_callback('#@if\(((?<=\().*(?=\)))\)#', function ($matches) {
            return '<?php if(' . $matches[1] . '): ?>';
        }, $template);

        $template = preg_replace_callback('#@else#', function ($matches) {
            return '<?php else: ?>';
        }, $template);

        $template = preg_replace_callback('#@elseif\(((?<=\().*(?=\)))\)#', function ($matches) {
            return '<?php elseif(' . $matches[1] . '): ?>';
        }, $template);

        // replace `@endif` with `endif;`
        $template = preg_replace_callback('#@endif#', function ($matches) {
            return '<?php endif; ?>';
        }, $template);

        // replace `@foreach` with `foreach(...):`
        $template = preg_replace_callback('#@foreach\(((?<=\().*(?=\)))\)#', function ($matches) {
            return '<?php foreach(' . $matches[1] . '): ?>';
        }, $template);

        // replace `@endforeach` with `endforeach;`
        $template = preg_replace_callback('#@endforeach#', function ($matches) {
            return '<?php endforeach; ?>';
        }, $template);

        // replace `@[anything](...)` with `$this->[anything](...)`
        $template = preg_replace_callback('#\s+@([^(]+)\(((?<=\().*(?=\)))\)#', function ($matches) {
            return '<?php $this->' . $matches[1] . '(' . $matches[2] . '); ?>';
        }, $template);

        // replace `{{ ... }}` with `print $this->escape(...)`
        $template = preg_replace_callback('#\{\{([^}]*)\}\}#', function ($matches) {
            return '<?php print $this->escape(' . $matches[1] . '); ?>';
        }, $template);

        // replace `{!! ... !!}` with `print ...`
        $template = preg_replace_callback('#\{!!([^}]+)!!\}#', function ($matches) {
            return '<?php print ' . $matches[1] . '; ?>';
        }, $template);

        // replace {! ... !} with `print number_format`
        $template = preg_replace_callback('#\{\!\s*([^}]+)\s*\!\}#', function ($matches) {
            return '<?php print number_format(' . $matches[1] . ', 2); ?>';
        }, $template);

        return $template;
    }
}
